<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Drive;
use App\Models\Folder;
use App\Models\RecentFile;
use App\Services\DocumentPreviewService;
use App\Services\FolderAccessResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FileController extends Controller
{
    public function upload()
    {
        try {
            return view('admin.files.upload');
        } catch (\Exception $e) {
            Log::error('FileController::upload failed: ' . $e->getMessage());
            return null;
        }
    }

    public function store(Request $request)
    {
        try {
        $parentId   = $request->input('parent_id');
        $parentType = $request->input('parent_type', 'folder');
        $files      = $request->file('file');

        if (!$files) {
            return response()->json(['message' => 'No files uploaded'], 400);
        }

        // normalize to array
        if (!is_array($files)) {
            $files = [$files];
        }

        // When uploading from the drive root, parent_id is the Drive DB id,
        // not a Folder id — such uploads land at the root (parent_item_id null).
        if ($parentType === 'drive') {
            $drive = Drive::find($parentId);
            if (!$drive) {
                return response()->json(['message' => 'Drive not found'], 404);
            }
            $parentDbId   = $parentId; // keep drive id so JS can inject into rootFolder node
            $parentFolder = null;      // no DB folder row for a root upload
        } else {
            $parentFolder = Folder::find($parentId);
            if (!$parentFolder) {
                return response()->json(['message' => 'Parent folder not found'], 404);
            }
            $parentDbId   = $parentFolder->id;
        }

        $results = [];

        foreach ($files as $file) {
            // try to read any relative path information (uploaded from webkitdirectory)
            $relativePath = $file->originalPath ?? ($file->getClientOriginalName() ?? $file->getFilename());

            $parts = explode('/', str_replace('\\', '/', $relativePath));
            $fileName = array_pop($parts);

            // Recreate any intermediate folders from the relative path as
            // local rows, so a directory upload keeps its shape.
            $currentParentId = $parentFolder?->id;
            foreach ($parts as $folderName) {
                $folderName = trim($folderName);
                if ($folderName === '') continue;

                $currentParentId = Folder::firstOrCreate(
                    [
                        'name'           => $folderName,
                        'parent_item_id' => $currentParentId,
                        'type'           => 'folder',
                    ],
                    ['created_by' => Auth::id()]
                )->id;
            }

            $created = Folder::create([
                'name'           => $fileName,
                'parent_item_id' => $currentParentId,
                'type'           => 'file',
                'size'           => $file->getSize() ?? 0,
                'created_by'     => Auth::id(),
            ]);

            // Persist the bytes at the flat, id-derived location the rest of
            // the app resolves through Folder::localDiskPath().
            $ext = pathinfo($fileName, PATHINFO_EXTENSION);
            $file->storeAs('project_folders', $created->id . ($ext !== '' ? '.' . $ext : ''), 'public');

            $results[] = [
                'status'    => 201,
                'response'  => ['name' => $fileName, 'size' => $created->size],
                'folder_id' => $created->id,
            ];
        }

        return response()->json(['results' => $results, 'parent_db_id' => $parentDbId]);
        } catch (\Exception $e) {
            Log::error('FileController::store failed: ' . $e->getMessage());
            return null;
        }
    }

    public function preview($id, FolderController $folderController)
    {
        try {
        $decoded = base64_decode($id, true);
        $fileId = $decoded !== false ? $decoded : $id;
        if (!ctype_digit((string) $fileId)) {
            abort(404);
        }

        $file = Folder::findOrFail((int) $fileId);

        if (!FolderAccessResolver::userCanAccessFolder(Auth::user(), $file->id)) {
            abort(403, 'You do not have permission to view this file.');
        }

        // No document server — PDFs are served as-is, office documents are
        // converted to PDF via LibreOffice headless (cached) and viewed the
        // same way, everything else falls back to "no preview available".
        $previewRelativePath = DocumentPreviewService::previewPath($file);
        $previewUrl = $previewRelativePath ? asset('storage/' . $previewRelativePath) : null;

        $existing = RecentFile::where('user_id', Auth::id())->where('file_id', $fileId)->first();
        if ($existing) {
            $existing->increment('view_count');
            $existing->touch();
        } else {
            RecentFile::create(['user_id' => Auth::id(), 'file_id' => $fileId, 'view_count' => 1]);
        }

        $breadcrumb = $file->getBreadcrumb(); // array of Folder models from root → file

        // Sidebar "Project Folders" tree data, so it can auto-expand to and
        // highlight this file instead of rendering empty on this page.
        $activeFileId = $file->id;
        $treeData = $folderController->sidebarTreeData();
        $rootFolderData = $treeData['rootFolderData'];

        return response()
            ->view('admin.files.preview', compact(
                'previewUrl',
                'file',
                'breadcrumb',
                'activeFileId',
                'rootFolderData'
            ))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('FileController::preview failed: ' . $e->getMessage());
            return null;
        }
    }

    public function updateFile(Request $request, $id)
    {
        try {
        $decoded = base64_decode($id, true);
        $fileId = $decoded !== false ? $decoded : $id;
        if (!ctype_digit((string) $fileId)) {
            abort(404);
        }

        $file = Folder::findOrFail((int) $fileId);

        $request->validate(['file' => 'required|file|max:102400']);

        $newFile = $request->file('file');

        $path = $file->localDiskPath();
        if (!$path) {
            return back()->with('error', 'This item is not a file.');
        }

        Storage::disk('public')->put($path, file_get_contents($newFile->getRealPath()));
        $file->update(['size' => $newFile->getSize() ?? 0]);

        return back()->with('success', 'File updated successfully.');
        } catch (\Exception $e) {
            Log::error('FileController::updateFile failed: ' . $e->getMessage());
            return null;
        }
    }

    public function download($id, $type)
    {
        try {
        $decoded = base64_decode($id, true);
        $fileId = $decoded !== false ? $decoded : $id;
        if (!ctype_digit((string) $fileId)) {
            abort(404);
        }

        $file = Folder::findOrFail((int) $fileId);

        if (!FolderAccessResolver::userCanAccessFolder(Auth::user(), $file->id)) {
            abort(403, 'You do not have permission to download this.');
        }

        $path = $file->localDiskPath();
        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'File not found on disk.');
        }

        \App\Services\ActivityLogger::logDownload(Auth::user(), $file);

        return Storage::disk('public')->download($path, $file->name);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('FileController::download failed: ' . $e->getMessage());
            return null;
        }
    }

    private function loadChildrenRecursive(Collection $nodes): void
    {
        try {
            if ($nodes->isEmpty()) {
                return;
            }

            $nodes->load('children');

            foreach ($nodes as $node) {
                if ($node->children && $node->children->isNotEmpty()) {
                    $this->loadChildrenRecursive($node->children);
                }
            }
        } catch (\Exception $e) {
            Log::error('FileController::loadChildrenRecursive failed: ' . $e->getMessage());
        }
    }
}
