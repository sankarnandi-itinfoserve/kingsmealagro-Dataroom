<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\RecentFile;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use ZipArchive;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Active == not soft-deleted; archived projects live in archived().
            $projects = $this->trueRootProjectsQuery()
                ->with('creator')
                ->latest()
                ->paginate(20)
                ->withQueryString();

            return view('admin.projects.index', compact('projects'));
        } catch (\Exception $e) {
            Log::error('ProjectController::index failed: ' . $e->getMessage());
            return null;
        }
    }

    public function create()
    {
        try {
            return view('admin.projects.create');
        } catch (\Exception $e) {
            Log::error('ProjectController::create failed: ' . $e->getMessage());
            return null;
        }
    }

    public function store(Request $request)
    {
        try {
        // Branch on which mode the user actually picked, rather than just
        // inferring it from whether a zip happens to be present — that way
        // a zip that failed to arrive (e.g. rejected by the server's
        // upload_max_filesize before Laravel ever sees it) gets an error
        // attached to the visible 'zip' field, not the hidden 'name' one.
        $setupMode = $request->input('setup_mode', 'manual');

        if ($setupMode === 'zip') {
            $zipFile = $request->file('zip');

            if (!$zipFile) {
                throw ValidationException::withMessages([
                    'zip' => 'Please choose a zip file to upload.',
                ]);
            }

            if (!$zipFile->isValid()) {
                $sizeErrorCodes = [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE];
                $message = in_array($zipFile->getError(), $sizeErrorCodes, true)
                    ? 'That zip file is too large for this server (max ' . ini_get('upload_max_filesize') . '). Please use a smaller file or ask an admin to raise the upload limit.'
                    : 'The zip file failed to upload. Please try again.';

                throw ValidationException::withMessages(['zip' => $message]);
            }

            $request->validate([
                'zip' => 'file|mimes:zip',
            ]);

            $projectName = pathinfo($zipFile->getClientOriginalName(), PATHINFO_FILENAME);
        } else {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);

            $zipFile = null;
            $projectName = trim((string) $request->name);
        }

        // A project is a root-level folder row: parent_item_id stays null.
        $folder = Folder::create([
            'name'           => $projectName,
            'type'           => 'folder',
            'parent_item_id' => null,
            'created_by'     => auth()->id(),
        ]);

        if ($zipFile) {
            $this->importZipIntoFolder($zipFile, $folder->id);
        }

        $successMessage = 'Folder "' . $folder->name . '" created successfully.';

        // The AJAX create form drives its own toast + redirect client-side —
        // returning a redirect()->with() here would get silently auto-followed
        // by the browser's XHR before the JS success handler ever runs,
        // consuming the one-time flash message before it could be shown.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMessage]);
        }

        return redirect()->route('projects.index')->with('success', $successMessage);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('ProjectController::store failed: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['message' => 'Something went wrong while creating the folder.'], 500);
            }

            return back()->withErrors(['zip' => 'Something went wrong while creating the folder.'])->withInput();
        }
    }

    /**
     * Extracts an uploaded zip and recreates its folder/file tree as Folder
     * rows nested under $parentId — same parent_item_id relation shape, and
     * same physical storage convention (Folder::localDiskPath(), the flat
     * project_folders/{id}.{ext} layout on the "public" disk) that manual
     * folder creation and the drag-and-drop file uploader already use.
     */
    private function importZipIntoFolder(UploadedFile $zipFile, int $parentId): void
    {
        set_time_limit(0);

        $extractPath = storage_path('app/tmp_zip_import_' . uniqid());

        $zip = new ZipArchive();
        if ($zip->open($zipFile->getRealPath()) !== true) {
            throw new \RuntimeException('Could not open the uploaded zip file.');
        }

        File::ensureDirectoryExists($extractPath);
        $zip->extractTo($extractPath);
        $zip->close();

        try {
            $this->importExtractedDirectory($this->resolveImportRoot($extractPath), $parentId);
        } finally {
            File::deleteDirectory($extractPath);
        }
    }

    /**
     * Most zip tools wrap a zipped folder's contents in a single top-level
     * directory (zipping a folder named "test" produces test.zip containing
     * just test/...). The project is already named after the zip file, so
     * importing that wrapper folder too would double the name in the tree
     * (Project "test" > "test" > ...). If extraction produced exactly one
     * real entry and it's a directory, import its contents directly instead
     * of nesting an extra nameless — or duplicate-named — folder for it.
     */
    private function resolveImportRoot(string $extractPath): string
    {
        $entries = array_values(array_filter($this->listRealEntries($extractPath)));

        if (count($entries) === 1 && is_dir($extractPath . DIRECTORY_SEPARATOR . $entries[0])) {
            return $extractPath . DIRECTORY_SEPARATOR . $entries[0];
        }

        return $extractPath;
    }

    /**
     * Directory entries with '.', '..', and common zip/OS junk filtered out.
     */
    private function listRealEntries(string $path): array
    {
        $entries = scandir($path);
        if ($entries === false) {
            return [];
        }

        return array_values(array_filter($entries, function ($entry) {
            return $entry !== '.' && $entry !== '..'
                && $entry !== '__MACOSX' && $entry !== '.DS_Store' && $entry !== 'Thumbs.db'
                && !str_starts_with($entry, '.');
        }));
    }

    private function importExtractedDirectory(string $path, int $parentId): void
    {
        $entries = $this->listRealEntries($path);
        natcasesort($entries);

        foreach ($entries as $entry) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($fullPath)) {
                $childFolder = Folder::create([
                    'name'           => $entry,
                    'type'           => 'folder',
                    'parent_item_id' => $parentId,
                    'created_by'     => auth()->id(),
                ]);

                $this->importExtractedDirectory($fullPath, $childFolder->id);
            } else {
                $childFile = Folder::create([
                    'name'           => $entry,
                    'type'           => 'file',
                    'parent_item_id' => $parentId,
                    'size'           => filesize($fullPath) ?: 0,
                    'created_by'     => auth()->id(),
                ]);

                $diskPath = $childFile->localDiskPath();
                if ($diskPath) {
                    Storage::disk('public')->put($diskPath, file_get_contents($fullPath));
                }
            }
        }
    }

    public function show(Folder $project)
    {
        try {
        // Collect all descendant IDs via the parent_item_id chain
        $allDescendants = $project->descendants();

        $fileIds = $allDescendants->where('type', 'file')->pluck('id');

        // Document Updates: files modified in the past 7 days
        $documentUpdates = $allDescendants
            ->where('type', 'file')
            ->filter(fn($f) => $f->updated_at && $f->updated_at->gte(now()->subDays(7)))
            ->sortByDesc('updated_at')
            ->take(10)
            ->values();

        // Top Documents by Views via recent_files
        $topDocuments = collect();
        if ($fileIds->isNotEmpty()) {
            $topDocuments = RecentFile::select('file_id', DB::raw('SUM(view_count) as view_count'))
                ->whereIn('file_id', $fileIds)
                ->groupBy('file_id')
                ->orderByDesc('view_count')
                ->limit(10)
                ->with('folder')
                ->get()
                ->map(fn($rf) => (object)[
                    'name'       => optional($rf->folder)->name ?? '—',
                    'view_count' => $rf->view_count,
                ]);
        }

        // Top Users by activity (views of files in this project)
        $topUsers = collect();
        if ($fileIds->isNotEmpty()) {
            $topUsers = RecentFile::select('user_id', DB::raw('SUM(view_count) as activity_count'))
                ->whereIn('file_id', $fileIds)
                ->groupBy('user_id')
                ->orderByDesc('activity_count')
                ->limit(10)
                ->with('user')
                ->get()
                ->map(fn($rf) => (object)[
                    'name'         => optional($rf->user)->fname
                                      ? trim($rf->user->fname . ' ' . $rf->user->lname)
                                      : (optional($rf->user)->username ?? '—'),
                    'email'        => optional($rf->user)->email ?? '',
                    'login_count'  => $rf->activity_count,
                ]);
        }

        return view('admin.projects.show', compact(
            'project', 'documentUpdates', 'topDocuments', 'topUsers'
        ));
        } catch (\Exception $e) {
            Log::error('ProjectController::show failed: ' . $e->getMessage());
            return null;
        }
    }

    public function edit(Folder $project)
    {
        try {
        $project->load('childrenRecursive');

        return view('admin.projects.edit', compact('project'));
        } catch (\Exception $e) {
            Log::error('ProjectController::edit failed: ' . $e->getMessage());
            return null;
        }
    }

    public function update(Request $request, Folder $project)
    {
        try {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $project->update([
            'name' => $request->name,
        ]);

        return redirect()->route('projects.index')->with('success', 'Folder updated successfully.');
        } catch (\Exception $e) {
            Log::error('ProjectController::update failed: ' . $e->getMessage());
            return null;
        }
    }

    public function destroy(Folder $project)
    {
        try {
            $project->delete();
            return redirect()->route('projects.index')->with('success', 'Folder deleted.');
        } catch (\Exception $e) {
            Log::error('ProjectController::destroy failed: ' . $e->getMessage());
            return null;
        }
    }

    public function archived(Request $request)
    {
        try {
        // Archived == soft-deleted; that's the only archive signal now.
        $projects = $this->trueRootProjectsQuery()
            ->onlyTrashed()
            ->with('creator')
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.projects.archived', compact('projects'));
        } catch (\Exception $e) {
            Log::error('ProjectController::archived failed: ' . $e->getMessage());
            return null;
        }
    }

    public function restoreDeleted($id)
    {
        try {
            $project = Folder::withTrashed()->findOrFail($id);
            $project->restore();

            return redirect()->route('projects.archived')
                ->with('success', 'Folder "' . $project->name . '" has been restored.');
        } catch (\Exception $e) {
            Log::error('ProjectController::restoreDeleted failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Archiving a project is now just a soft delete — the old archived_at /
     * retention_days / status bookkeeping columns no longer exist.
     */
    public function archive(Request $request, Folder $project)
    {
        try {
            $project->delete();

            return redirect()->route('projects.index')
                ->with('success', 'Folder "' . $project->name . '" has been moved to the archive.');
        } catch (\Exception $e) {
            Log::error('ProjectController::archive failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Archived projects are soft-deleted, so route-model binding would never
     * resolve one — look it up withTrashed by id instead.
     */
    public function restore($id)
    {
        try {
            $project = Folder::withTrashed()->findOrFail($id);
            $project->restore();

            return redirect()->route('projects.archived')
                ->with('success', 'Folder "' . $project->name . '" has been restored to active.');
        } catch (\Exception $e) {
            Log::error('ProjectController::restore failed: ' . $e->getMessage());
            return null;
        }
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    /**
     * A top-level project is a folder with no parent row at all —
     * parent_item_id is a self-referential FK to folders.id, so a null
     * means root level. Same check DashboardController::index() and
     * AnalyticsController use everywhere else in the app.
     */
    private function trueRootProjectsQuery()
    {
        try {
            return Folder::where('type', 'folder')
                ->whereNull('parent_item_id');
        } catch (\Exception $e) {
            Log::error('ProjectController::trueRootProjectsQuery failed: ' . $e->getMessage());
            return null;
        }
    }
}
