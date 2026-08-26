<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Folder;
use App\Models\RecentFile;
use App\Models\User;
use App\Models\UserAuthLog;
use App\Traits\FormatsActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AnalyticsController extends Controller
{
    use FormatsActivityLogTrait;


    // ── Global overview ────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
        $totalLogins    = UserAuthLog::count();
        $uniqueUsers    = UserAuthLog::distinct('user_id')->count('user_id');
        $totalViews     = RecentFile::sum('view_count');

        // A top-level project is a folder with no parent row at all
        // (parent_item_id is a self-referential FK to folders.id).
        // Active vs archived is purely a soft-delete distinction now.
        $activeProjects = Folder::whereNull('parent_item_id')
            ->where('type', 'folder')
            ->count();

        $activityByDay = UserAuthLog::selectRaw('DATE(logged_in) as date, COUNT(*) as count')
            ->where('logged_in', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $topUsers = UserAuthLog::selectRaw('user_id, COUNT(*) as login_count')
            ->groupBy('user_id')
            ->orderByDesc('login_count')
            ->limit(5)
            ->with('user')
            ->get();

        $topDocuments = RecentFile::selectRaw('file_id, SUM(view_count) as total_views')
            ->whereHas('folder', fn($q) => $q->where('type', 'file'))
            ->groupBy('file_id')
            ->orderByDesc('total_views')
            ->limit(5)
            ->with('folder')
            ->get();

        $recentActivity = UserAuthLog::with('user')
            ->latest('logged_in')
            ->limit(10)
            ->get();

        $projects = Folder::whereNull('parent_item_id')
            ->where('type', 'folder')
            ->orderBy('name')
            ->get(['id', 'name', 'updated_at'])
            ->each(function ($proj) {
                $proj->total_size = $proj->descendants()
                    ->where('type', 'file')
                    ->sum('size');
            });

        if ($request->get('export') === 'csv') {
            return $this->exportGlobalCsv($topUsers, $topDocuments);
        }

        return view('admin.analytics.index', compact(
            'totalLogins', 'uniqueUsers', 'totalViews', 'activeProjects',
            'activityByDay', 'topUsers', 'topDocuments', 'recentActivity', 'projects'
        ));
        } catch (\Exception $e) {
            Log::error('AnalyticsController::index failed: ' . $e->getMessage());
            return null;
        }
    }

    // ── Per-project analytics ──────────────────────────────────────────────────

    public function project(Request $request, Folder $project)
    {
        try {
        $allDescendants = $project->descendants();
        $fileIds        = $allDescendants->where('type', 'file')->pluck('id');

        $activityByDay = collect();
        if ($fileIds->isNotEmpty()) {
            $activityByDay = RecentFile::selectRaw('DATE(updated_at) as date, SUM(view_count) as count')
                ->whereIn('file_id', $fileIds)
                ->where('updated_at', '>=', now()->subDays(30))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
        }

        $topFiles = collect();
        if ($fileIds->isNotEmpty()) {
            $topFiles = RecentFile::selectRaw('file_id, SUM(view_count) as total_views')
                ->whereIn('file_id', $fileIds)
                ->groupBy('file_id')
                ->orderByDesc('total_views')
                ->limit(10)
                ->with('folder')
                ->get();
        }

        $topUsers = collect();
        if ($fileIds->isNotEmpty()) {
            $topUsers = RecentFile::selectRaw('user_id, SUM(view_count) as activity_count')
                ->whereIn('file_id', $fileIds)
                ->groupBy('user_id')
                ->orderByDesc('activity_count')
                ->limit(10)
                ->with('user')
                ->get();
        }

        if ($request->get('export') === 'csv') {
            return $this->exportProjectCsv($project, $topFiles, $topUsers);
        }

        // Every add/update/delete/upload under this project — same source
        // data as the main Activity Logs page, just scoped to this
        // project's own folder plus everything nested inside it.
        $projectFolderIds = $allDescendants->pluck('id')->push($project->id);

        $projectLogsBase = ActivityLog::where('model_type', Folder::class)
            ->whereIn('model_id', $projectFolderIds);

        // Full option lists for the Activity/Type/User column filter panels,
        // drawn from all of this project's logs (not just the current page)
        // — same pattern as the Activity Logs / per-user analytics pages.
        $activityOptions = (clone $projectLogsBase)
            ->whereNotNull('action')
            ->distinct()
            ->pluck('action')
            ->sort()
            ->values();
        $selectedActivity = array_values(array_intersect((array) $request->input('activity', []), $activityOptions->all()));

        $modelTypesRaw = (clone $projectLogsBase)->distinct()->pluck('model_type');
        $modelOptions = $modelTypesRaw->map(fn ($type) => class_basename($type))->unique()->sort()->values();
        $modelTypeMap = $modelTypesRaw->mapWithKeys(fn ($type) => [strtolower(class_basename($type)) => $type]);

        $selectedTypeKeys = array_values(array_filter((array) $request->input('type', [])));
        $selectedModelTypes = collect($selectedTypeKeys)
            ->map(fn ($key) => $modelTypeMap[$key] ?? null)
            ->filter()
            ->values()
            ->all();

        $userNamesRaw = (clone $projectLogsBase)->whereNotNull('user_name')->distinct()->pluck('user_name');
        $userOptions = $userNamesRaw->unique()->sort()->values();
        $userNameMap = $userNamesRaw->mapWithKeys(fn ($name) => [strtolower($name) => $name]);

        $selectedUserKeys = array_values(array_filter((array) $request->input('user', [])));
        $selectedUserNames = collect($selectedUserKeys)
            ->map(fn ($key) => $userNameMap[$key] ?? null)
            ->filter()
            ->values()
            ->all();

        // The Activity/Type/User column filters are real query-string filters
        // (not a client-side row toggle) so they narrow the actual paginated
        // result set and compose correctly with withQueryString() — a
        // client-only toggle would only ever affect the 10 rows already on
        // screen, leaving every other page unfiltered.
        $projectLogs = (clone $projectLogsBase)
            ->with('user')
            ->when($selectedActivity, fn ($q) => $q->whereIn('action', $selectedActivity))
            ->when($selectedModelTypes, fn ($q) => $q->whereIn('model_type', $selectedModelTypes))
            ->when($selectedUserNames, fn ($q) => $q->whereIn('user_name', $selectedUserNames))
            ->latest()
            ->paginate(10, ['*'], 'logsPage')
            ->withQueryString();

        [$logDescriptions, $logPaths] = $this->buildLinkedDescriptions($projectLogs->getCollection());

        return view('admin.analytics.project', compact(
            'project', 'activityByDay', 'topFiles', 'topUsers',
            'projectLogs', 'logDescriptions', 'logPaths',
            'activityOptions', 'modelOptions', 'userOptions'
        ));
        } catch (\Exception $e) {
            Log::error('AnalyticsController::project failed: ' . $e->getMessage());
            return null;
        }
    }

    // ── Per-user drill-down ────────────────────────────────────────────────────

    public function userDetail(Request $request, $userId)
    {
        try {
        $user = User::withTrashed()->findOrFail($userId);
        $totalLogins = UserAuthLog::where('user_id', $user->id)->count();
        $totalViews  = RecentFile::where('user_id', $user->id)->sum('view_count');
        $lastSeen    = UserAuthLog::where('user_id', $user->id)->max('logged_in');

        $loginHistory = UserAuthLog::where('user_id', $user->id)
            ->orderByDesc('logged_in')
            ->limit(50)
            ->get();

        $docAccess = RecentFile::where('user_id', $user->id)
            ->orderByDesc('view_count')
            ->with('folder')
            ->limit(50)
            ->get();

        $activityByDay = UserAuthLog::selectRaw('DATE(logged_in) as date, COUNT(*) as count')
            ->where('user_id', $user->id)
            ->where('logged_in', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Full option list for the "Type" column filter panel — drawn from all
        // of this user's logs, not just the current page, same as the
        // Activity Logs list (see ActivityLogController::renderLogs). Also
        // doubles as the lookup used to turn a checked "type" checkbox value
        // (a lowercased class basename) back into the real model_type string
        // the column filter's query needs to match against.
        $modelTypesRaw = ActivityLog::where('user_id', $user->id)
            ->whereNotNull('model_type')
            ->distinct()
            ->pluck('model_type');

        $modelOptions = $modelTypesRaw->map(fn ($type) => class_basename($type))
            ->unique()
            ->sort()
            ->values();

        $modelTypeMap = $modelTypesRaw->mapWithKeys(fn ($type) => [strtolower(class_basename($type)) => $type]);

        // The Activity/Type column filters are real query-string filters
        // (not a client-side row toggle) so they narrow the actual paginated
        // result set — a client-only toggle would only ever affect the 25
        // rows already on screen, leaving every other page unfiltered and
        // making "Next" look broken as soon as a filter was applied.
        $actionOptions = ['created', 'updated', 'deleted', 'restored', 'login', 'logout', 'password_changed'];
        $selectedActions = array_values(array_intersect((array) $request->input('action', []), $actionOptions));

        // A "type" checkbox value is either a plain class basename ("folder")
        // or, for the combined UserAuthLog option, the literal "login,logout"
        // — split every selected value on "," so both shapes normalize the
        // same way before being sorted into model-type vs. auth-action matches.
        $selectedTypes = collect((array) $request->input('model', []))
            ->flatMap(fn ($v) => explode(',', $v))
            ->filter()
            ->unique();

        $selectedAuthActions = $selectedTypes->intersect(['login', 'logout'])->values()->all();
        $selectedModelTypes = $selectedTypes->reject(fn ($v) => in_array($v, ['login', 'logout']))
            ->map(fn ($v) => $modelTypeMap[$v] ?? null)
            ->filter()
            ->values()
            ->all();

        $activityLogs = ActivityLog::where('user_id', $user->id)
            ->when($selectedActions, fn ($q) => $q->whereIn('action', $selectedActions))
            ->when($selectedModelTypes || $selectedAuthActions, function ($q) use ($selectedModelTypes, $selectedAuthActions) {
                $q->where(function ($q2) use ($selectedModelTypes, $selectedAuthActions) {
                    if ($selectedModelTypes) {
                        $q2->orWhereIn('model_type', $selectedModelTypes);
                    }
                    if ($selectedAuthActions) {
                        $q2->orWhere(function ($q3) use ($selectedAuthActions) {
                            $q3->where('model_type', UserAuthLog::class)
                                ->whereIn('action', $selectedAuthActions);
                        });
                    }
                });
            })
            ->latest()
            ->paginate(25)
            ->withQueryString();

        [$descriptions, $paths] = $this->buildLinkedDescriptions($activityLogs->getCollection());

        if ($request->get('export') === 'csv') {
            return $this->exportUserCsv($user, $loginHistory, $docAccess);
        }

        return view('admin.analytics.user', compact(
            'user', 'totalLogins', 'totalViews', 'lastSeen',
            'loginHistory', 'docAccess', 'activityByDay',
            'activityLogs', 'descriptions', 'paths', 'modelOptions'
        ));
        } catch (\Exception $e) {
            Log::error('AnalyticsController::userDetail failed: ' . $e->getMessage());
            return null;
        }
    }

    // ── CSV helpers ────────────────────────────────────────────────────────────

    private function exportGlobalCsv($topUsers, $topDocuments)
    {
        try {
            $rows   = [['Report', 'Global Analytics — Top Users & Documents'], []];
            $rows[] = ['#', 'User', 'Email', 'Login Count'];
            foreach ($topUsers as $i => $u) {
                $name   = optional($u->user)->fname
                    ? trim(optional($u->user)->fname . ' ' . optional($u->user)->lname)
                    : (optional($u->user)->email ?? '—');
                $rows[] = [$i + 1, $name, optional($u->user)->email ?? '', $u->login_count];
            }
            $rows[] = [];
            $rows[] = ['#', 'Document', 'Total Views'];
            foreach ($topDocuments as $i => $d) {
                $rows[] = [$i + 1, optional($d->folder)->name ?? '—', $d->total_views];
            }
            return $this->csvResponse('analytics-global.csv', $rows);
        } catch (\Exception $e) {
            Log::error('AnalyticsController::exportGlobalCsv failed: ' . $e->getMessage());
            return null;
        }
    }

    private function exportProjectCsv(Folder $project, $topFiles, $topUsers)
    {
        try {
            $rows   = [['Project', $project->name], []];
            $rows[] = ['#', 'File', 'Total Views'];
            foreach ($topFiles as $i => $f) {
                $rows[] = [$i + 1, optional($f->folder)->name ?? '—', $f->total_views];
            }
            $rows[] = [];
            $rows[] = ['#', 'User', 'Email', 'Activity (views)'];
            foreach ($topUsers as $i => $u) {
                $name   = optional($u->user)->fname
                    ? trim(optional($u->user)->fname . ' ' . optional($u->user)->lname)
                    : (optional($u->user)->email ?? '—');
                $rows[] = [$i + 1, $name, optional($u->user)->email ?? '', $u->activity_count];
            }
            return $this->csvResponse('analytics-' . str($project->name)->slug() . '.csv', $rows);
        } catch (\Exception $e) {
            Log::error('AnalyticsController::exportProjectCsv failed: ' . $e->getMessage());
            return null;
        }
    }

    private function exportUserCsv(User $user, $loginHistory, $docAccess)
    {
        try {
            $name   = $user->fname ? trim($user->fname . ' ' . $user->lname) : ($user->email ?? 'user');
            $rows   = [['User', $name, $user->email ?? ''], []];
            $rows[] = ['Type', 'Detail', 'Timestamp'];
            foreach ($loginHistory as $log) {
                $rows[] = ['Login', $log->logon_type ?? 'login', $log->logged_in];
            }
            foreach ($docAccess as $rf) {
                $rows[] = ['Document View', optional($rf->folder)->name ?? '—', $rf->updated_at];
            }
            return $this->csvResponse('analytics-user-' . $user->id . '.csv', $rows);
        } catch (\Exception $e) {
            Log::error('AnalyticsController::exportUserCsv failed: ' . $e->getMessage());
            return null;
        }
    }

    private function csvResponse(string $filename, array $rows)
    {
        try {
            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                foreach ($rows as $row) {
                    fputcsv($out, array_map('strval', $row));
                }
                fclose($out);
            }, $filename, ['Content-Type' => 'text/csv']);
        } catch (\Exception $e) {
            Log::error('AnalyticsController::csvResponse failed: ' . $e->getMessage());
            return null;
        }
    }
}
