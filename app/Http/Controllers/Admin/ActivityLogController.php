<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\UserAuthLog;
use App\Traits\FormatsActivityLogTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    use FormatsActivityLogTrait;

    public function index(Request $request)
    {
        try {
            abort_if(!auth()->user()->hasAnyRole(['admin', 'super-admin']), 403);

            return $this->renderLogs($request, null, 'activity-logs.index', 'Activity Logs');
        } catch (\Exception $e) {
            Log::error('ActivityLogController::index failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Same view/filters/UI as index() above, scoped to only the logged-in
     * user's own entries — the non-admin sidebar's "My Activity" link.
     * Deliberately no role check: it's always scoped to the caller's own
     * user_id, so there's nothing to leak regardless of who views it.
     */
    public function myActivity(Request $request)
    {
        try {
            return $this->renderLogs(
                $request,
                auth()->id(),
                'my-activity.index',
                'My Activity',
                'Every add, update, delete and upload you\'ve made, newest first.'
            );
        } catch (\Exception $e) {
            Log::error('ActivityLogController::myActivity failed: ' . $e->getMessage());
            return null;
        }
    }

    private function renderLogs(Request $request, ?int $userId, string $routeName, string $pageTitle, ?string $pageSubtitle = null)
    {
        try {
        // Full option lists for the column filter panels — drawn from the
        // whole table, not just the current page, so a filter still works
        // for values that don't happen to appear on the visible 25 rows.
        // Also doubles as the lookup used to turn a checked "Type" checkbox
        // value (a lowercased class basename) back into the real model_type
        // string the column filter's query needs to match against.
        $modelTypesRaw = ActivityLog::when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereNotNull('model_type')
            ->distinct()
            ->pluck('model_type');

        $modelOptions = $modelTypesRaw->map(fn ($type) => class_basename($type))
            ->unique()
            ->sort()
            ->values();

        $modelTypeMap = $modelTypesRaw->mapWithKeys(fn ($type) => [strtolower(class_basename($type)) => $type]);

        $userNamesRaw = ActivityLog::when($userId, fn ($q) => $q->where('user_id', $userId))
            ->whereNotNull('user_name')
            ->distinct()
            ->pluck('user_name');

        $userOptions = $userNamesRaw->unique()->sort()->values();
        $userNameMap = $userNamesRaw->mapWithKeys(fn ($name) => [strtolower($name) => $name]);

        // The Activity/Type/User column filters are real query-string filters
        // (not a client-side row toggle) so they narrow the actual paginated
        // result set — a client-only toggle would only ever affect the 25
        // rows already on screen, leaving every other page unfiltered and
        // making "Next" look broken as soon as a filter was applied. They use
        // their own query keys (activity/type/user) rather than reusing the
        // filter bar's "action" field, since that one stays a single-select
        // tied to the stats-strip toggle above.
        $activityOptions = ['created', 'updated', 'deleted', 'restored', 'login', 'logout', 'password_changed'];
        $selectedActivity = array_values(array_intersect((array) $request->input('activity', []), $activityOptions));

        // A "type" checkbox value is either a plain class basename ("folder")
        // or, for the combined UserAuthLog option, the literal "login,logout"
        // — split every selected value on "," so both shapes normalize the
        // same way before being sorted into model-type vs. auth-action matches.
        $selectedTypes = collect((array) $request->input('type', []))
            ->flatMap(fn ($v) => explode(',', $v))
            ->filter()
            ->unique();

        $selectedAuthActions = $selectedTypes->intersect(['login', 'logout'])->values()->all();
        $selectedModelTypes = $selectedTypes->reject(fn ($v) => in_array($v, ['login', 'logout']))
            ->map(fn ($v) => $modelTypeMap[$v] ?? null)
            ->filter()
            ->values()
            ->all();

        $selectedUserKeys = array_values(array_filter((array) $request->input('user', [])));
        $selectedUserNames = collect($selectedUserKeys)
            ->map(fn ($key) => $userNameMap[$key] ?? null)
            ->filter()
            ->values()
            ->all();

        $logs = ActivityLog::with('user')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($request->filled('action'), fn ($q) => $q->where('action', $request->input('action')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = $request->input('search');
                $q->where(function ($q2) use ($term) {
                    $q2->where('description', 'like', "%{$term}%")
                        ->orWhere('user_name', 'like', "%{$term}%")
                        ->orWhere('model_type', 'like', "%{$term}%");
                });
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->input('to')))
            ->when($selectedActivity, fn ($q) => $q->whereIn('action', $selectedActivity))
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
            ->when($selectedUserNames, fn ($q) => $q->whereIn('user_name', $selectedUserNames))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $actionCounts = ActivityLog::when($userId, fn ($q) => $q->where('user_id', $userId))
            ->selectRaw('action, count(*) as total')
            ->groupBy('action')
            ->pluck('total', 'action');

        [$descriptions, $paths] = $this->buildLinkedDescriptions($logs->getCollection());

        return view('admin.activity_logs.index', compact(
            'logs', 'actionCounts', 'descriptions', 'paths', 'modelOptions', 'userOptions',
            'routeName', 'pageTitle', 'pageSubtitle'
        ));
        } catch (\Exception $e) {
            Log::error('ActivityLogController::renderLogs failed: ' . $e->getMessage());
            return null;
        }
    }
}
