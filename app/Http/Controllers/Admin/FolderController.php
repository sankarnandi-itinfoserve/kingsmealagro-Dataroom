<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Folder;
use App\Services\FolderAccessResolver;
use Illuminate\Http\Request;
use App\Models\FolderShare;
use Illuminate\Support\Facades\Mail;
use App\Mail\FolderSharedMail;
use App\Models\Drive;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FolderController extends Controller
{
    private array $currentUserFavoriteIds = [];

    public function index(Request $request, $parent_id = null)
    {
        try {
        $this->currentUserFavoriteIds = Favorite::where('user_id', auth()->id())
            ->pluck('folder_id')
            ->toArray();

        $drives = Drive::where('name', 'Documents')->first();

        // Root-level folders this user can see — every real root folder for
        // super-admin/admin, or just their granted folders (standing in as
        // their own roots) for everyone else. See FolderAccessResolver.
        $allFolders = FolderAccessResolver::visibleRootFolders(auth()->user());

        $isAdmin = auth()->user()->hasAnyRole(['super-admin', 'admin']);
        $granted = $isAdmin ? null : FolderAccessResolver::grantedFolderIds(auth()->user());

        $this->loadChildrenRecursive($allFolders, $granted);

        $rootFolderData = [
            'id' => $drives->id ?? 0,
            'type' => 'folder',
            'name' => $drives->name ?? 'Documents',
            'children' => $allFolders->map(fn($item) => $this->mapFolderNode($item))->values()->all(),
        ];

        return view('admin.folders.drive', compact(
            'drives',
            'allFolders',
            'rootFolderData'
        ));
        } catch (\Exception $e) {
            Log::error('FolderController::index failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Same rootFolderData shape as index() builds for the Drive page —
     * reusable by any other page that needs to render the "Project Folders"
     * sidebar tree (e.g. the file preview page).
     */
    public function sidebarTreeData(): array
    {
        try {
            $this->currentUserFavoriteIds = Favorite::where('user_id', auth()->id())
                ->pluck('folder_id')
                ->toArray();

            $drives = Drive::where('name', 'Documents')->first();

            // Root-level folders this user can see — see index() for why.
            $allFolders = FolderAccessResolver::visibleRootFolders(auth()->user());

            $isAdmin = auth()->user()->hasAnyRole(['super-admin', 'admin']);
            $granted = $isAdmin ? null : FolderAccessResolver::grantedFolderIds(auth()->user());

            $this->loadChildrenRecursive($allFolders, $granted);

            $rootFolderData = [
                'id' => $drives->id ?? 0,
                'type' => 'folder',
                'name' => $drives->name ?? 'Documents',
                'children' => $allFolders->map(fn($item) => $this->mapFolderNode($item))->values()->all(),
            ];

            return [
                'rootFolderData' => $rootFolderData,
            ];
        } catch (\Exception $e) {
            Log::error('FolderController::sidebarTreeData failed: ' . $e->getMessage());
            return [
                'rootFolderData' => ['id' => 0, 'type' => 'folder', 'name' => 'Documents', 'children' => []],
            ];
        }
    }

    /**
     * $granted is the exact set of folder/file ids explicitly granted to the
     * current user — null means unrestricted (admins). When restricted, a
     * child (folder or file) is only kept if it's itself in $granted —
     * nothing is shown just because its parent is visible.
     */
    private function loadChildrenRecursive(Collection $nodes, ?array $granted = null): void
    {
        try {
            if ($nodes->isEmpty()) {
                return;
            }

            $nodes->load('children');

            foreach ($nodes as $node) {
                if (!$node->children || $node->children->isEmpty()) {
                    continue;
                }

                if ($granted !== null) {
                    $visibleChildren = $node->children->filter(
                        fn ($child) => in_array($child->id, $granted, true)
                    )->values();
                    $node->setRelation('children', $visibleChildren);
                }

                if ($node->children->isNotEmpty()) {
                    $this->loadChildrenRecursive($node->children, $granted);
                }
            }
        } catch (\Exception $e) {
            Log::error('FolderController::loadChildrenRecursive failed: ' . $e->getMessage());
        }
    }

    private function mapFolderNode(Folder $node): ?array
    {
        try {
        // The local creator row is the only source of authorship.
        $localCreator = trim(($node->creator->fname ?? '') . ' ' . ($node->creator->lname ?? ''));
        $creator = $localCreator !== '' ? $localCreator : 'System';

        $type = $node->type ?? 'folder';
        $ext = '';
        if ($type === 'file') {
            $parts = explode('.', $node->name);
            $ext = count($parts) > 1 ? strtolower(end($parts)) : '';
            $sizeValue = (int) ($node->size ?? 0);
        } else {
            $sizeValue = $this->sumChildrenSize($node);
        }

        return [
            'id' => $node->id,
            'type' => $type,
            'ext' => $ext,
            'name' => $node->name,
            'parentId' => $node->parent_item_id,
            'size' => $this->formatBytes($sizeValue),
            'sizeValue' => $sizeValue,
            'modified' => optional($node->updated_at)->format('m/d/Y h:i A') ?? '-',
            'modifiedTs' => optional($node->updated_at)->timestamp ?? 0,
            'creator' => $creator,
            'favorite' => in_array($node->id, $this->currentUserFavoriteIds),
            'children' => $node->children
                ? $node->children->map(fn($child) => $this->mapFolderNode($child))->values()->all()
                : [],
        ];
        } catch (\Exception $e) {
            Log::error('FolderController::mapFolderNode failed: ' . $e->getMessage());
            return null;
        }
    }

    private function sumChildrenSize(Folder $node): ?int
    {
        try {
            $total = 0;
            foreach ($node->children ?? [] as $child) {
                if (($child->type ?? 'folder') === 'file') {
                    $total += (int) ($child->size ?? 0);
                } else {
                    $total += $this->sumChildrenSize($child);
                }
            }
            return $total;
        } catch (\Exception $e) {
            Log::error('FolderController::sumChildrenSize failed: ' . $e->getMessage());
            return null;
        }
    }

    private function formatBytes(int $bytes): ?string
    {
        try {
            if ($bytes <= 0) {
                return '0 KB';
            }

            $units = ['B', 'KB', 'MB', 'GB', 'TB'];
            $power = (int) floor(log($bytes, 1024));
            $power = max(0, min($power, count($units) - 1));
            $value = $bytes / (1024 ** $power);

            return $power === 0
                ? number_format($value, 0) . ' ' . $units[$power]
                : number_format($value, 1) . ' ' . $units[$power];
        } catch (\Exception $e) {
            Log::error('FolderController::formatBytes failed: ' . $e->getMessage());
            return null;
        }
    }

    public function store(Request $request)
    {
        try {
        $request->validate(['name' => 'required']);

        $parentType     = $request->input('parent_type', 'folder');
        $parentFolder   = null;

        if ($parentType !== 'drive') {
            $parentFolder = $request->parent_id ? Folder::find($request->parent_id) : null;
        }

        // parent_item_id is a self-referential FK to folders.id — null at root.
        $folder = Folder::create([
            'name'           => $request->name,
            'parent_item_id' => $parentFolder?->id,
            'type'           => 'folder',
            'created_by'     => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'folder'  => [
                'id'          => $folder->id,
                'type'        => 'folder',
                'ext'         => '',
                'name'        => $folder->name,
                'parentId'    => $folder->parent_item_id,
                'size'        => '0 KB',
                'sizeValue'   => 0,
                'modified'    => now()->format('m/d/Y h:i A'),
                'modifiedTs'  => now()->timestamp,
                'creator'     => '',
                'favorite'    => false,
                'webUrl'      => null,
                'children'    => [],
            ],
        ]);
        } catch (\Exception $e) {
            Log::error('FolderController::store failed: ' . $e->getMessage());
            return null;
        }
    }

    // ✏ Rename
    public function update(Request $request, $id)
    {
        try {
            Folder::findOrFail($id)->update([
                'name' => $request->name
            ]);

            return back();
        } catch (\Exception $e) {
            Log::error('FolderController::update failed: ' . $e->getMessage());
            return null;
        }
    }

    public function destroy($id)
    {
        try {
            $folder = Folder::findOrFail($id);

            $error = $this->deleteFolderEverywhere($folder);
            if ($error) {
                return response()->json(['message' => $error], 502);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('FolderController::destroy failed: ' . $e->getMessage());
            return null;
        }
    }

    public function toggleFavorite(Request $request)
    {
        try {
        $fav = Favorite::where([
            'user_id' => auth()->id(),
            'folder_id' => $request->id
        ])->first();

        if ($fav) {
            $fav->delete();
            return response()->json(['success' => true]);
        }

        Favorite::create([
            'user_id' => auth()->id(),
            'folder_id' => $request->id
        ]);

        return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('FolderController::toggleFavorite failed: ' . $e->getMessage());
            return null;
        }
    }

    public function favorites(Request $request, $parent_id = null)
    {
        try {
        // Not just folders — a favorited file with no favorited ancestor
        // folder is its own root here too, same as the star button on the
        // Drive page lets you favorite either one independently.
        $favoriteRoots = Folder::with(['children', 'favorites'])
            ->whereHas('favorites', function ($q) {
                $q->where('user_id', auth()->id());
            })
            ->get()
            ->filter(fn ($folder) => FolderAccessResolver::userCanAccessFolder(auth()->user(), $folder->id))
            ->values();

        $this->loadFavoriteChildrenRecursive($favoriteRoots);

        $favoriteRootData = [
            'id' => 0,
            'type' => 'folder',
            'name' => 'Favorite Folders',
            'children' => $favoriteRoots
                ->map(fn($item) => $this->mapFavoriteFolderNode($item))
                ->values()
                ->all(),
        ];

        $allfolders = Folder::select('id', 'name')->get();

        return view('admin.folders.favorite', compact(
            'allfolders',
            'favoriteRootData'
        ));
        } catch (\Exception $e) {
            Log::error('FolderController::favorites failed: ' . $e->getMessage());
            return null;
        }
    }

    private function loadFavoriteChildrenRecursive(Collection $nodes): void
    {
        try {
            if ($nodes->isEmpty()) {
                return;
            }

            $nodes->load(['children', 'favorites']);

            foreach ($nodes as $node) {
                if ($node->children && $node->children->isNotEmpty()) {
                    $this->loadFavoriteChildrenRecursive($node->children);
                }
            }
        } catch (\Exception $e) {
            Log::error('FolderController::loadFavoriteChildrenRecursive failed: ' . $e->getMessage());
        }
    }

    private function mapFavoriteFolderNode(Folder $node): ?array
    {
        try {
        $isFile = $node->type === 'file';
        // The local creator is the only source — see mapFolderNode().
        $localCreator = trim(($node->creator->fname ?? '') . ' ' . ($node->creator->lname ?? ''));
        $creator = $localCreator !== '' ? $localCreator : 'System';

        $sizeValue = (int) ($node->size ?? 0);
        $favorite = $node->favorites
            ? $node->favorites->contains('user_id', auth()->id())
            : false;

        $ext = ltrim((string) pathinfo((string) $node->name, PATHINFO_EXTENSION), '.');

        $children = !$isFile && $node->children
            ? $node->children
            ->map(fn($child) => $this->mapFavoriteFolderNode($child))
            ->values()
            ->all()
            : [];

        // Ancestor folders only (self excluded) — lets the Favorites page
        // show where this item actually lives, since a favorited item can
        // be buried several folders deep with no favorited parent shown
        // anywhere else in this tree.
        $breadcrumb = collect($node->getBreadcrumb())->slice(0, -1)
            ->map(fn($f) => ['id' => $f->id, 'name' => $f->name])
            ->values()
            ->all();

        return [
            'id' => $node->id,
            'type' => $isFile ? 'file' : 'folder',
            'name' => $node->name,
            'ext' => strtolower($ext),
            'size' => $this->formatBytes($sizeValue),
            'sizeValue' => $sizeValue,
            'modified' => optional($node->updated_at)->format('m/d/Y h:i A') ?? '-',
            'modifiedTs' => optional($node->updated_at)->timestamp ?? 0,
            'creator' => trim($creator) ?: 'System',
            'favorite' => $favorite,
            'previewUrl' => $isFile ? '/files/' . base64_encode($node->id) . '/preview' : null,
            'breadcrumb' => $breadcrumb,
            'children' => $children,
        ];
        } catch (\Exception $e) {
            Log::error('FolderController::mapFavoriteFolderNode failed: ' . $e->getMessage());
            return null;
        }
    }


    /**
     * Zip a folder (with its whole sub-tree of files) or a single file and
     * stream it back. Files are stored flat under project_folders/{id}.{ext};
     * the in-zip name is rebuilt from the item's breadcrumb below the folder
     * being zipped, so the archive keeps the tree shape the user sees.
     */
    public function downloadZip($id)
    {
        try {
            $folder = Folder::findOrFail($id);

            if (!FolderAccessResolver::userCanAccessFolder(auth()->user(), $folder->id)) {
                abort(403, 'You do not have permission to download this.');
            }

            $files = $folder->type === 'file'
                ? collect([$folder])
                : $folder->descendants()->where('type', 'file');

            $zip     = new ZipArchive();
            $zipPath = storage_path('app/tmp_zip_' . uniqid() . '.zip');

            if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                foreach ($files as $file) {
                    $path = $file->localDiskPath();
                    if ($path && Storage::disk('public')->exists($path)) {
                        $zip->addFile(
                            Storage::disk('public')->path($path),
                            $this->zipEntryName($file, $folder->id)
                        );
                    }
                }
                $zip->close();
            }

            return response()->download($zipPath, $folder->name . '.zip')->deleteFileAfterSend(true);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('FolderController::downloadZip failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * In-zip path for a file: its breadcrumb from just below $rootId down to
     * the file itself, so same-named files in different sub-folders don't
     * collide inside the archive. Falls back to the bare name if the root
     * isn't found in the chain (e.g. the item IS the root).
     */
    private function zipEntryName(Folder $file, $rootId): string
    {
        try {
            $breadcrumb = $file->getBreadcrumb() ?: [];
            $names      = [];
            $seenRoot   = false;

            foreach ($breadcrumb as $node) {
                if (!$seenRoot) {
                    if ((string) $node->id === (string) $rootId) {
                        $seenRoot = true;
                    }
                    continue;
                }
                $names[] = $node->name;
            }

            return $names ? implode('/', $names) : $file->name;
        } catch (\Exception $e) {
            Log::error('FolderController::zipEntryName failed: ' . $e->getMessage());
            return $file->name;
        }
    }
    public function share(Request $request)
    {
        try {
        $ids = explode(',', $request->folder_ids);
        $emails = explode(',', $request->emails);

        foreach ($ids as $folderId) {

            $folder = Folder::findOrFail($folderId);

            foreach ($emails as $email) {

                $email = trim($email);

                // Save share
                FolderShare::create([
                    'folder_id' => $folderId,
                    'email' => $email,
                    'permission' => $request->permission
                ]);

                // 🔗 Generate access link
                $link = route('shared.folders', ['parent_id' => $folderId]);

                // 📧 Send email
                Mail::to($email)->queue(new FolderSharedMail($folder, $link, $request->permission));
            }
        }

        return back()->with('success', 'Folders shared successfully & email sent');
        } catch (\Exception $e) {
            Log::error('FolderController::share failed: ' . $e->getMessage());
            return null;
        }
    }
    public function rename(Request $request)
    {
        try {
        $request->validate([
            'id'   => 'required',
            'name' => 'required|string|max:255',
        ]);

        $folder = Folder::findOrFail($request->id);

        $newName = trim($request->name);

        $folder->update(['name' => $newName]);

        return response()->json([
            'success' => true,
            'message' => 'Renamed successfully',
            'folder'  => ['id' => $folder->id, 'name' => $folder->name],
        ]);
        } catch (\Exception $e) {
            Log::error('FolderController::rename failed: ' . $e->getMessage());
            return null;
        }
    }
    public function move(Request $request)
    {
        try {
            $folder = Folder::findOrFail($request->id);
            // parent_id is the destination Folder's primary key from the
            // client; parent_item_id is a self-referential FK to folders.id.
            $destFolder = Folder::findOrFail($request->parent_id);
            $folder->update(['parent_item_id' => $destFolder->id]);

            return back()->with('success', 'Moved successfully');
        } catch (\Exception $e) {
            Log::error('FolderController::move failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * "Copy to..." — duplicates a file/folder row (and, for a folder, its
     * whole sub-tree) into the destination. Purely a local DB operation —
     * copying never touches the filesystem.
     */
    public function copyItem(Request $request)
    {
        try {
        $request->validate(['id' => 'required']);

        $source = Folder::findOrFail($request->id);

        // Destination parent's own primary key — null means the root level.
        $destLocalParentId = null;
        if ($request->filled('destination_id')) {
            $dest = Folder::find($request->destination_id);
            if (!$dest || $dest->type !== 'folder') {
                return response()->json(['message' => 'Invalid destination folder.'], 422);
            }
            $destLocalParentId = $dest->id;
        }

        // Same-name conflict in the destination — ask the caller how to
        // resolve it instead of guessing.
        $conflictResolution = $request->input('conflict_resolution'); // null | 'overwrite' | 'rename'
        $existing = Folder::whereRaw('LOWER(name) = ?', [strtolower($source->name)])
            ->when(
                $destLocalParentId === null,
                fn($q) => $q->whereNull('parent_item_id'),
                fn($q) => $q->where('parent_item_id', $destLocalParentId)
            )
            ->first();

        if ($existing && !$conflictResolution) {
            return response()->json([
                'conflict'     => true,
                'message'      => 'An item named "' . $source->name . '" already exists in this location.',
                'existingName' => $existing->name,
            ], 409);
        }

        $copyName = $source->name;

        if ($existing && $conflictResolution === 'overwrite') {
            $error = $this->deleteFolderEverywhere($existing);
            if ($error) {
                return response()->json(['message' => $error], 502);
            }
        } elseif ($existing && $conflictResolution === 'rename') {
            $copyName = $this->generateUniqueCopyName($source->name, $destLocalParentId);
        }

        $newFolder = Folder::create([
            'name'           => $copyName,
            'parent_item_id' => $destLocalParentId,
            'type'           => $source->type,
            'size'           => $source->size ?? 0,
            'created_by'     => auth()->id(),
        ]);

        if ($newFolder->type === 'folder') {
            $this->copySubtreeLocally($source, $newFolder->id);
        } else {
            $this->copyLocalFileBytes($source, $newFolder);
        }

        $this->loadChildrenRecursive(new \Illuminate\Database\Eloquent\Collection([$newFolder]));

        return response()->json([
            'success'       => true,
            'message'       => 'Copied successfully',
            'item'          => $this->mapFolderNode($newFolder),
            'destinationId' => $destLocalParentId,
        ]);
        } catch (\Exception $e) {
            Log::error('FolderController::copyItem failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * "Move to..." — like copyItem() (same destination picker, same name-
     * conflict resolution), but the item only ever exists in one place, so
     * only its own parent_item_id changes. Children never need touching:
     * they point at this folder's own id, not at wherever it lives.
     */
    public function moveItem(Request $request)
    {
        try {
        $request->validate(['id' => 'required']);

        $source = Folder::findOrFail($request->id);

        // Destination parent's own primary key — null means the root level.
        $destLocalParentId = null;
        if ($request->filled('destination_id')) {
            $dest = Folder::find($request->destination_id);
            if (!$dest || $dest->type !== 'folder') {
                return response()->json(['message' => 'Invalid destination folder.'], 422);
            }
            if ((int) $dest->id === (int) $source->id) {
                return response()->json(['message' => 'Cannot move an item into itself.'], 422);
            }
            if ($source->type === 'folder' && $source->descendants()->contains('id', $dest->id)) {
                return response()->json(['message' => 'Cannot move a folder into one of its own sub-folders.'], 422);
            }
            $destLocalParentId = $dest->id;
        }

        if ((int) $source->parent_item_id === (int) $destLocalParentId) {
            return response()->json(['message' => 'This item is already in that location.'], 422);
        }

        $conflictResolution = $request->input('conflict_resolution');
        $existing = Folder::where('id', '!=', $source->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($source->name)])
            ->when(
                $destLocalParentId === null,
                fn($q) => $q->whereNull('parent_item_id'),
                fn($q) => $q->where('parent_item_id', $destLocalParentId)
            )
            ->first();

        if ($existing && !$conflictResolution) {
            return response()->json([
                'conflict'     => true,
                'message'      => 'An item named "' . $source->name . '" already exists in this location.',
                'existingName' => $existing->name,
            ], 409);
        }

        $newName = $source->name;

        if ($existing && $conflictResolution === 'overwrite') {
            $error = $this->deleteFolderEverywhere($existing);
            if ($error) {
                return response()->json(['message' => $error], 502);
            }
        } elseif ($existing && $conflictResolution === 'rename') {
            $newName = $this->generateUniqueCopyName($source->name, $destLocalParentId);
        }

        $oldParentId = $source->parent_item_id;

        $source->update([
            'parent_item_id' => $destLocalParentId,
            'name'           => $newName,
        ]);

        return response()->json([
            'success'       => true,
            'message'       => 'Moved successfully',
            'id'            => $source->id,
            'name'          => $source->name,
            'oldParentId'   => $oldParentId,
            'destinationId' => $destLocalParentId,
        ]);
        } catch (\Exception $e) {
            Log::error('FolderController::moveItem failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Duplicate every descendant of $source under the already-created copy
     * identified by $newParentId — parent before children, so each new row's
     * parent_item_id is always resolvable as we go.
     */
    private function copySubtreeLocally(Folder $source, int $newParentId): void
    {
        try {
        foreach ($source->children as $child) {
            $childCopy = Folder::create([
                'name'           => $child->name,
                'parent_item_id' => $newParentId,
                'type'           => $child->type,
                'size'           => $child->size ?? 0,
                'created_by'     => auth()->id(),
            ]);

            if ($childCopy->type === 'folder') {
                $this->copySubtreeLocally($child, $childCopy->id);
            } elseif ($childCopy->type === 'file') {
                $this->copyLocalFileBytes($child, $childCopy);
            }
        }
        } catch (\Exception $e) {
            Log::error('FolderController::copySubtreeLocally failed: ' . $e->getMessage());
        }
    }

    /**
     * Copies a file's physical bytes on the public disk from $source's
     * location to $copy's — needed because copying only duplicates the
     * Folder row, which gets a new id and therefore a new localDiskPath().
     */
    private function copyLocalFileBytes(Folder $source, Folder $copy): void
    {
        try {
            $oldPath = $source->localDiskPath();
            $newPath = $copy->localDiskPath();

            if ($oldPath && $newPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->copy($oldPath, $newPath);
            }
        } catch (\Exception $e) {
            Log::error('FolderController::copyLocalFileBytes failed: ' . $e->getMessage());
        }
    }

    /**
     * Windows Explorer/Google Drive-style conflict-resolution name: the
     * first collision gets " (Copy)", every collision after that appends a
     * bare "(Copy)" with no space — "Name (Copy)", "Name (Copy)(Copy)", ...
     */
    private function generateUniqueCopyName(string $originalName, ?int $parentId): ?string
    {
        try {
        $ext  = '';
        $base = $originalName;
        $dot  = strrpos($originalName, '.');
        if ($dot !== false && $dot > 0) {
            $base = substr($originalName, 0, $dot);
            $ext  = substr($originalName, $dot);
        }

        $suffix    = '';
        $candidate = $originalName;
        while ($this->nameExistsInParent($candidate, $parentId)) {
            $suffix   .= ($suffix === '' ? ' (Copy)' : '(Copy)');
            $candidate = $base . $suffix . $ext;
        }

        return $candidate;
        } catch (\Exception $e) {
            Log::error('FolderController::generateUniqueCopyName failed: ' . $e->getMessage());
            return null;
        }
    }

    private function nameExistsInParent(string $name, ?int $parentId): ?bool
    {
        try {
            return Folder::whereRaw('LOWER(name) = ?', [strtolower($name)])
                ->when($parentId === null, fn($q) => $q->whereNull('parent_item_id'), fn($q) => $q->where('parent_item_id', $parentId))
                ->exists();
        } catch (\Exception $e) {
            Log::error('FolderController::nameExistsInParent failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Deletes a folder/file and all its descendants. Returns an error message
     * on failure, null on success. Shared by destroy() and copyItem()'s
     * "Overwrite" conflict resolution.
     */
    private function deleteFolderEverywhere(Folder $folder): ?string
    {
        try {
        $descendants = $folder->descendants();

        foreach ($descendants as $descendant) {
            $descendant->delete();
        }
        $folder->delete();

        return null;
        } catch (\Exception $e) {
            Log::error('FolderController::deleteFolderEverywhere failed: ' . $e->getMessage());
            return null;
        }
    }

    public function copyMultiple(Request $request)
    {
        try {

            // ✅ FIX: normalize ids
            $ids = $request->ids;

            if (is_string($ids)) {
                $ids = explode(',', $ids);
            }

            if (!$ids || count($ids) === 0) {
                return response()->json(['message' => 'No folders selected'], 400);
            }

            foreach ($ids as $folderId) {

                $folder = Folder::with(['childrenRecursive'])->find($folderId);

                if (!$folder) continue;

                $this->copyFolderRecursive($folder, null);
            }

            return response()->json(['message' => 'Folders copied successfully']);
        } catch (\Exception $e) {

            Log::error('Copy Multiple Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function copyFolderRecursive($folder, $parentId = null)
    {
        try {
            $newFolder = Folder::create([
                'name' => $folder->name . ' (Copy)',
                'parent_item_id' => $parentId,
                'type' => $folder->type ?? 'folder',
                'size' => $folder->size ?? 0,
                'created_by' => auth()->id(),
            ]);

            if ($newFolder->type === 'file') {
                $this->copyLocalFileBytes($folder, $newFolder);
            }

            // ✅ Recursive children
            foreach ($folder->childrenRecursive as $child) {
                $this->copyFolderRecursive($child, $newFolder->id);
            }
        } catch (\Exception $e) {
            Log::error('FolderController::copyFolderRecursive failed: ' . $e->getMessage());
        }
    }
    /**
     * Bulk zip download — every selected file, plus every file beneath every
     * selected folder, in one archive.
     */
    public function downloadMultiple(Request $request)
    {
        try {
        $ids = array_filter(explode(',', $request->ids));

        if (empty($ids)) {
            return back()->with('error', 'No items selected');
        }

        $zip     = new ZipArchive();
        $zipPath = storage_path('app/tmp_zip_' . uniqid() . '.zip');

        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            return back()->with('error', 'Could not create zip file.');
        }

        foreach ($ids as $id) {
            $item = Folder::find($id);
            if (!$item || !FolderAccessResolver::userCanAccessFolder(auth()->user(), $item->id)) {
                continue;
            }

            \App\Services\ActivityLogger::logDownload(auth()->user(), $item);

            $files = $item->type === 'file'
                ? collect([$item])
                : $item->descendants()->where('type', 'file');

            foreach ($files as $file) {
                $path = $file->localDiskPath();
                if ($path && Storage::disk('public')->exists($path)) {
                    // Prefix with the selected item's own name so two
                    // selections sharing a filename stay distinct.
                    $inZip = $item->type === 'file'
                        ? $file->name
                        : $item->name . '/' . $this->zipEntryName($file, $item->id);

                    $zip->addFile(Storage::disk('public')->path($path), $inZip);
                }
            }
        }

        $zip->close();

        return response()->download($zipPath, 'download.zip')->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('FolderController::downloadMultiple failed: ' . $e->getMessage());
            return null;
        }
    }

}
