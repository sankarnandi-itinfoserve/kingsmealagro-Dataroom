<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Favorite;
use App\Models\Folder;
use App\Models\RecentFile;
use App\Models\User;
use App\Models\UserAuthLog;
use App\Services\FolderAccessResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index()
    {
        try {
        $authUser = auth()->user();

        // Recent files for current user (needed by all roles)
        $recentFiles = RecentFile::with('folder')
            ->where('user_id', Auth::id())
            ->latest()
            ->take(20)
            ->get()
            ->filter(fn($r) => $r->folder !== null)
            ->values();

        // Only admin / super-admin get the org-wide numbers below. Every
        // other role (user, product-owner, security-reviewer, etc.) gets the
        // same look, scoped to exactly what's been granted to them.
        if (!$authUser->hasAnyRole(['admin', 'super-admin'])) {
            $granted = FolderAccessResolver::grantedFolderIds($authUser);

            $activeProjects = empty($granted) ? 0 : Folder::whereIn('id', $granted)
                ->whereNull('parent_item_id')
                ->where('type', 'folder')
                ->count();

            $totalFiles = empty($granted) ? 0 : Folder::whereIn('id', $granted)
                ->where('type', 'file')
                ->count();

            $favoritesCount = Favorite::where('user_id', $authUser->id)->count();

            $loginsToday = UserAuthLog::where('user_id', $authUser->id)
                ->whereDate('logged_in', today())
                ->count();

            $recentProjects = FolderAccessResolver::visibleRootFolders($authUser)
                ->where('type', 'folder')
                ->sortByDesc('updated_at')
                ->take(6)
                ->values();

            $fileCounts = $this->countGrantedFilesPerProject($recentProjects, $granted);

            $recentActivity = ActivityLog::where('user_id', $authUser->id)
                ->latest()
                ->take(4)
                ->get();

            return view('admin.dashboard_user', compact(
                'authUser',
                'activeProjects',
                'totalFiles',
                'favoritesCount',
                'loginsToday',
                'recentProjects',
                'fileCounts',
                'recentActivity',
                'recentFiles',
            ));
        }

        // Admin / Super-admin get the full dashboard
        $totalUsers  = User::whereNull('deleted_at')->count();
        $totalFiles  = Folder::where('type', 'file')->count();
        $loginsToday = UserAuthLog::whereDate('logged_in', today())->count();

        // A top-level project is a folder with no parent row at all
        // (parent_item_id is a self-referential FK to folders.id).
        // Active vs archived is purely a soft-delete distinction now.
        $activeProjects = Folder::whereNull('parent_item_id')
            ->where('type', 'folder')
            ->count();

        $archivedProjects = Folder::onlyTrashed()
            ->whereNull('parent_item_id')
            ->where('type', 'folder')
            ->count();

        $recentProjects = Folder::whereNull('parent_item_id')
            ->where('type', 'folder')
            ->latest('updated_at')
            ->take(6)
            ->get();

        $fileCounts = $this->countFilesPerProject($recentProjects);

        $recentUsers = User::latest()->take(4)->get();

        return view('admin.dashboard', compact(
            'authUser',
            'totalUsers',
            'totalFiles',
            'activeProjects',
            'archivedProjects',
            'loginsToday',
            'recentProjects',
            'fileCounts',
            'recentUsers',
            'recentFiles',
        ));
        } catch (\Exception $e) {
            Log::error('DashboardController::index failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Total files nested anywhere under each project (not just ones placed
     * directly in the project root) — walked via parent_item_id, the
     * self-referential FK to folders.id. Loads every folder/file row once
     * and counts in memory rather than one query per project.
     */
    private function countFilesPerProject($projects): ?array
    {
        try {
            $childrenByParentId = Folder::select('id', 'parent_item_id', 'type')
                ->get()
                ->groupBy('parent_item_id');

            $countRecursive = function ($folderId) use (&$countRecursive, $childrenByParentId) {
                $children = $childrenByParentId->get($folderId, collect());
                $count = $children->where('type', 'file')->count();
                foreach ($children->where('type', 'folder') as $child) {
                    $count += $countRecursive($child->id);
                }
                return $count;
            };

            $counts = [];
            foreach ($projects as $project) {
                $counts[$project->id] = $countRecursive($project->id);
            }

            return $counts;
        } catch (\Exception $e) {
            Log::error('DashboardController::countFilesPerProject failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Same idea as countFilesPerProject(), but only counts files the user
     * was actually, individually granted (access no longer inherits from a
     * parent folder — see FolderAccessResolver) — otherwise a project card
     * would show a file count the user can't actually see.
     */
    private function countGrantedFilesPerProject($projects, array $granted): ?array
    {
        try {
            if (empty($granted)) {
                return [];
            }

            $rows = Folder::select('id', 'parent_item_id', 'type')->get()->keyBy('id');
            $grantedFileIds = Folder::whereIn('id', $granted)->where('type', 'file')->pluck('id');

            // Walk each granted file up to its ultimate root ancestor and
            // tally counts there, so a project card shows exactly how many
            // of its descendant files this user can actually open.
            $counts = [];
            foreach ($grantedFileIds as $fileId) {
                $current = $rows->get($fileId);
                $rootId  = $fileId;
                $visited = [];

                while ($current && $current->parent_item_id) {
                    if (in_array($current->parent_item_id, $visited, true)) {
                        break;
                    }
                    $visited[] = $current->parent_item_id;
                    $rootId  = $current->parent_item_id;
                    $current = $rows->get($current->parent_item_id);
                }

                $counts[$rootId] = ($counts[$rootId] ?? 0) + 1;
            }

            $result = [];
            foreach ($projects as $project) {
                $result[$project->id] = $counts[$project->id] ?? 0;
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('DashboardController::countGrantedFilesPerProject failed: ' . $e->getMessage());
            return null;
        }
    }
}
