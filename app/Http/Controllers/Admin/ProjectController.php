<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\RecentFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // A project is a root-level folder row: parent_item_id stays null.
        $folder = Folder::create([
            'name'           => $request->name,
            'type'           => 'folder',
            'parent_item_id' => null,
            'created_by'     => auth()->id(),
        ]);

        return redirect()->route('projects.index')->with('success', 'Project "' . $folder->name . '" created successfully.');
        } catch (\Exception $e) {
            Log::error('ProjectController::store failed: ' . $e->getMessage());
            return null;
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

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
        } catch (\Exception $e) {
            Log::error('ProjectController::update failed: ' . $e->getMessage());
            return null;
        }
    }

    public function destroy(Folder $project)
    {
        try {
            $project->delete();
            return redirect()->route('projects.index')->with('success', 'Project deleted.');
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
                ->with('success', 'Project "' . $project->name . '" has been restored.');
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
                ->with('success', 'Project "' . $project->name . '" has been moved to the archive.');
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
                ->with('success', 'Project "' . $project->name . '" has been restored to active.');
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
