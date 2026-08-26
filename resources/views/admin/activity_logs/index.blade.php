@extends('admin.layouts.app')

@section('title', $pageTitle ?? 'Activity Logs')
@section('page_title', $pageTitle ?? 'Activity Logs')

@push('addOnCss')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <style>
        .al-filter-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            flex-wrap: wrap;
        }

        .al-filter-bar input[type="text"],
        .al-filter-bar .al-date-input,
        .al-filter-bar select {
            height: 34px;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            padding: 0 10px;
            font-size: 12.5px;
            color: #334155;
            outline: none;
        }

        .al-filter-bar input[type="text"] {
            min-width: 220px;
            flex: 1;
        }

        .al-filter-bar input.al-date-input {
            min-width: 130px;
            flex: 0 0 auto;
        }

        .al-filter-btn {
            height: 34px;
            padding: 0 16px;
            border-radius: 8px;
            border: none;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
        }

        .al-filter-btn.apply {
            background: linear-gradient(135deg, #253447, #1a2737);
            color: #fff;
        }

        .al-filter-btn.reset {
            background: #fff;
            border: 1px solid #dbe4f0;
            color: #64748b;
        }

        .al-action-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .al-action-badge.created {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }

        .al-action-badge.updated {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fcd34d;
        }

        .al-action-badge.deleted {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }

        .al-action-badge.restored {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        .al-action-badge.login {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #6ee7b7;
        }

        .al-action-badge.logout {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .al-action-badge.password_changed {
            background: #ede9fe;
            color: #6d28d9;
            border: 1px solid #c4b5fd;
        }

        .al-action-badge.downloaded {
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
        }

        .al-model-tag {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 600;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e5e7eb;
        }

        .al-desc-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 2px 10px 2px 7px;
            border-radius: 7px;
            background: #fff;
            border: 1px solid #e2e8f0;
            color: #334155;
            text-decoration: none;
            transition: border-color .15s, box-shadow .15s;
            font-style: italic;
        }

        .al-desc-link i {
            font-size: 13px;
        }

        .al-desc-link:hover {
            border-color: #cbd5e1;
            box-shadow: 0 1px 5px rgba(15, 23, 42, .1);
            color: #1e293b;
        }

        /* Deleted item — same chip, but non-clickable and visually muted */
        .al-desc-link-inactive {
            cursor: default;
            background: #f8fafc;
            opacity: .75;
        }

        .al-desc-link-inactive:hover {
            border-color: #e2e8f0;
            box-shadow: none;
            color: #334155;
        }

        .al-detail-btn {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            border: 1px solid #dbe4f0;
            background: #fff;
            color: #253447;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .al-detail-btn:hover {
            background: #253447;
            color: #fff;
            border-color: #253447;
        }

        .al-detail-table {
            width: 100%;
            font-size: 12.5px;
            border-collapse: collapse;
        }

        .al-detail-table th,
        .al-detail-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eef2f7;
            text-align: left;
            vertical-align: top;
        }

        .al-detail-table th {
            width: 140px;
            color: #64748b;
            font-weight: 600;
            white-space: nowrap;
        }

        .al-detail-old {
            color: #b91c1c;
            text-decoration: line-through;
            display: block;
        }

        .al-detail-new {
            color: #15803d;
            display: block;
        }

        .al-detail-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 20px;
            padding: 12px 14px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 14px;
            font-size: 12.5px;
        }

        .al-detail-meta span strong {
            color: #253447;
        }

        /* ── Column sort / filter (Action, Model, User) ──────────────────────── */
        .col-has-filter {
            position: relative;
        }

        .col-th-inner {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 4px;
            white-space: nowrap;
        }

        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .sortable i {
            margin-left: 6px;
            color: #94a3b8;
        }

        .col-filter-btn {
            background: none;
            border: none;
            padding: 3px 5px;
            cursor: pointer;
            color: #b0bec5;
            border-radius: 4px;
            font-size: 10px;
            line-height: 1;
            flex-shrink: 0;
            transition: all .15s;
        }

        .col-filter-btn:hover {
            color: #253447;
            background: rgba(37, 52, 71, .1);
        }

        .col-filter-btn.active {
            color: #c0272d;
            background: #fef2f2;
        }

        .col-filter-panel {
            display: none;
            position: fixed;
            width: 220px;
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 10px;
            box-shadow: 0 8px 28px rgba(37, 52, 71, .15);
            z-index: 9999;
            overflow: hidden;
        }

        .col-filter-panel.open {
            display: block;
        }

        .cfp-header {
            padding: 9px 13px 7px;
            font-size: 11px;
            font-weight: 700;
            color: #253447;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .cfp-body {
            padding: 10px 13px;
            max-height: 190px;
            overflow-y: auto;
        }

        .cfp-checkboxes {
            padding: 8px 13px;
        }

        .cfp-check-label {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 6px 4px;
            cursor: pointer;
            font-size: 12px;
            color: #334155;
            border-radius: 5px;
            transition: background .1s;
        }

        .cfp-check-label:hover {
            background: #f1f5f9;
        }

        .cfp-check-label input[type="checkbox"] {
            accent-color: #253447;
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .cfp-footer {
            display: flex;
            gap: 6px;
            padding: 8px 12px;
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .cfp-reset {
            flex: 1;
            padding: 6px 0;
            border: 1px solid #dbe4f0;
            border-radius: 6px;
            background: #fff;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
            text-align: center;
            text-decoration: none;
            display: inline-block;
        }

        .cfp-reset:hover {
            border-color: #94a3b8;
            color: #334155;
        }

        .cfp-apply {
            flex: 1;
            padding: 6px 0;
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #253447, #1a2737);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .15s;
        }

        .cfp-apply:hover {
            opacity: .85;
        }

        /* ── Stats strip ──────────────────────────────────────────────────────── */
        .al-stats-strip {
            display: flex;
            flex-wrap: nowrap;
            gap: 10px;
            padding: 16px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
            overflow-x: auto;
        }

        .al-stat-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 12px;
            background: #fff;
            border: 1px solid #eef2f7;
            box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
            flex: 1 1 0;
            min-width: 0;
            transition: box-shadow .15s, transform .15s, border-color .15s;
            text-decoration: none;
            color: inherit;
            cursor: pointer;
        }

        .al-stat-card-active {
            border-color: var(--stat-color);
            box-shadow: 0 0 0 2px var(--stat-bg);
        }

        .al-stat-card:hover {
            box-shadow: 0 4px 14px rgba(15, 23, 42, .08);
            transform: translateY(-1px);
        }

        .al-stat-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--stat-bg);
            color: var(--stat-color);
            font-size: 14px;
            flex-shrink: 0;
        }

        .al-stat-val {
            font-size: 19px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.15;
        }

        .al-stat-label {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .03em;
            line-height: 1.3;
        }

        /* ── User column chip ────────────────────────────────────────────────── */
        .al-user-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 3px 11px 3px 3px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #253447;
            font-size: 12.5px;
            text-decoration: none;
            transition: background .15s, box-shadow .15s;
        }

        .al-user-link:hover {
            background: #e6edf7;
            box-shadow: 0 1px 3px rgba(15, 23, 42, .1);
            color: #1a2737;
        }

        .al-user-avatar-img {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
        }

        .al-user-avatar-icon {
            font-size: 20px;
            color: #94a3b8;
            line-height: 1;
            flex-shrink: 0;
        }
    </style>
@endpush

@section('content')
    @php $isMyActivity = ($routeName ?? 'activity-logs.index') === 'my-activity.index'; @endphp
    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card rol-card">

            {{-- ── Card header ── --}}
            <div class="rol-card-header">
                <div class="rol-card-header-icon">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="flex-grow-1">
                    <span style="color:#fff;font-size:15px;font-weight:700;">{{ $pageTitle ?? 'Activity Logs' }}</span>
                    <p class="rol-card-sub mb-0">
                        {{ $pageSubtitle ?? 'Every add, update, delete and upload across the system, newest first.' }}
                    </p>
                </div>
            </div>

            {{-- ── Stats strip — admin-wide Activity Logs page only, not My Activity ── --}}
            @unless ($isMyActivity)
                <div class="al-stats-strip">
                    @foreach ([['key' => 'created', 'label' => 'Created', 'icon' => 'fa-plus', 'color' => '#15803d', 'bg' => 'rgba(21,128,61,.12)'], ['key' => 'updated', 'label' => 'Updated', 'icon' => 'fa-pen', 'color' => '#b45309', 'bg' => 'rgba(180,83,9,.12)'], ['key' => 'deleted', 'label' => 'Deleted', 'icon' => 'fa-trash', 'color' => '#b91c1c', 'bg' => 'rgba(185,28,28,.12)'], ['key' => 'restored', 'label' => 'Restored', 'icon' => 'fa-rotate-left', 'color' => '#1e40af', 'bg' => 'rgba(30,64,175,.12)'], ['key' => 'downloaded', 'label' => 'Downloaded', 'icon' => 'fa-download', 'color' => '#0369a1', 'bg' => 'rgba(3,105,161,.12)'], ['key' => 'login', 'label' => 'Login', 'icon' => 'fa-right-to-bracket', 'color' => '#047857', 'bg' => 'rgba(4,120,87,.12)'], ['key' => 'logout', 'label' => 'Logout', 'icon' => 'fa-right-from-bracket', 'color' => '#475569', 'bg' => 'rgba(71,85,105,.12)'], ['key' => 'password_changed', 'label' => 'Password Changed', 'icon' => 'fa-key', 'color' => '#6d28d9', 'bg' => 'rgba(109,40,217,.12)']] as $stat)
                        @php $isActiveStat = request('action') === $stat['key']; @endphp
                        <a href="{{ request()->fullUrlWithQuery(['action' => $isActiveStat ? null : $stat['key']]) }}"
                            class="al-stat-card {{ $isActiveStat ? 'al-stat-card-active' : '' }}"
                            style="--stat-color:{{ $stat['color'] }};--stat-bg:{{ $stat['bg'] }};">
                            <div class="al-stat-icon"><i class="fa-solid {{ $stat['icon'] }}"></i></div>
                            <div>
                                <div class="al-stat-val">{{ $actionCounts[$stat['key']] ?? 0 }}</div>
                                <div class="al-stat-label">{{ $stat['label'] }}</div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- ── Filter bar ── --}}
            <form method="GET" action="{{ route($routeName ?? 'activity-logs.index') }}" class="al-filter-bar">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Search description, user, model…">

                @unless ($isMyActivity)
                    <select name="action">
                        <option value="">All actions</option>
                        <option value="created" {{ request('action') === 'created' ? 'selected' : '' }}>Created
                        </option>
                        <option value="updated" {{ request('action') === 'updated' ? 'selected' : '' }}>Updated
                        </option>
                        <option value="deleted" {{ request('action') === 'deleted' ? 'selected' : '' }}>Deleted
                        </option>
                        <option value="restored" {{ request('action') === 'restored' ? 'selected' : '' }}>Restored
                        </option>
                        <option value="downloaded"
                            {{ request('action') === 'downloaded' ? 'selected' : '' }}>Downloaded</option>
                        <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Login</option>
                        <option value="logout" {{ request('action') === 'logout' ? 'selected' : '' }}>Logout</option>
                        <option value="password_changed"
                            {{ request('action') === 'password_changed' ? 'selected' : '' }}>Password Changed
                        </option>
                    </select>
                @endunless

                <input type="text" name="from" class="al-date-input" data-flatpickr autocomplete="off"
                    value="{{ request('from') }}" placeholder="From date" title="From date">
                <input type="text" name="to" class="al-date-input" data-flatpickr autocomplete="off"
                    value="{{ request('to') }}" placeholder="To date" title="To date">

                {{-- Mirror the column filter panels' current selections so submitting
                     this form doesn't wipe them out — they live in a separate <form>
                     further down the page (see "Column filter panels" below). --}}
                @foreach ((array) request('activity', []) as $v)
                    <input type="hidden" name="activity[]" value="{{ $v }}">
                @endforeach
                @foreach ((array) request('type', []) as $v)
                    <input type="hidden" name="type[]" value="{{ $v }}">
                @endforeach
                @foreach ((array) request('user', []) as $v)
                    <input type="hidden" name="user[]" value="{{ $v }}">
                @endforeach

                <button type="submit" class="al-filter-btn apply"><i class="fa fa-filter me-1"></i>Filter</button>
                <a href="{{ route($routeName ?? 'activity-logs.index') }}"
                    class="al-filter-btn reset text-decoration-none d-inline-flex align-items-center">Reset</a>
            </form>

            {{-- ── Table section ── --}}
            <section class="fb-main rol-table-section">
                @if ($logs->count())
                    <div class="table-responsive">
                        <table class="table rol-table align-middle">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th class="sortable col-has-filter" data-sort="action">
                                        <div class="col-th-inner">
                                            <span>Activity <i class="fa-solid fa-sort sort-icon"></i></span>
                                            <button type="button"
                                                class="col-filter-btn {{ request()->filled('activity') ? 'active' : '' }}"
                                                data-col="action" title="Filter"><i
                                                    class="fa-solid fa-filter"></i></button>
                                        </div>
                                    </th>
                                    <th class="sortable col-has-filter" data-sort="model">
                                        <div class="col-th-inner">
                                            <span>Type <i class="fa-solid fa-sort sort-icon"></i></span>
                                            <button type="button"
                                                class="col-filter-btn {{ request()->filled('type') ? 'active' : '' }}"
                                                data-col="model" title="Filter"><i
                                                    class="fa-solid fa-filter"></i></button>
                                        </div>
                                    </th>
                                    <th>Description</th>
                                    <th class="sortable col-has-filter" data-sort="user">
                                        <div class="col-th-inner">
                                            <span>User <i class="fa-solid fa-sort sort-icon"></i></span>
                                            <button type="button"
                                                class="col-filter-btn cfp-right {{ request()->filled('user') ? 'active' : '' }}"
                                                data-col="user" title="Filter"><i
                                                    class="fa-solid fa-filter"></i></button>
                                        </div>
                                    </th>
                                    <th>IP</th>
                                    <th class="text-center">Details</th>
                                </tr>
                            </thead>
                            <tbody id="activityLogsBody">
                                @foreach ($logs as $log)
                                    <tr data-action="{{ $log->action }}" data-model="{{ strtolower($log->model_label) }}"
                                        data-user="{{ strtolower($log->user_name ?? 'system') }}">
                                        <td>
                                            <span style="font-size:12px;color:#64748b;white-space:nowrap;"
                                                title="{{ $log->created_at->format('d M Y, h:i:s A') }}">
                                                {{ $log->created_at->diffForHumans() }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="al-action-badge {{ $log->action }}">{{ str_replace('_', ' ', $log->action) }}</span>
                                        </td>
                                        <td>
                                            <span class="al-model-tag">{{ $log->model_label }}</span>
                                        </td>
                                        <td>
                                            <span style="font-size:13px;color:#334155;">{!! $descriptions[$log->id] ?? e($log->description) !!}</span>
                                            @if (!empty($paths[$log->id]))
                                                <div style="font-size:11px;color:#64758d;margin-top:3px;">
                                                    <i class="fa-solid fa-angle-right"
                                                        style="font-size:9px;margin-right:3px;"></i>{{ $paths[$log->id] }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($log->user_id)
                                                <a href="{{ route('analytics.userDetail', $log->user_id) }}"
                                                    class="al-user-link">
                                                    @if ($log->user && $log->user->avatar)
                                                        <img src="{{ asset('storage/' . $log->user->avatar) }}"
                                                            class="al-user-avatar-img" alt="">
                                                    @else
                                                        <i class="fa-solid fa-circle-user al-user-avatar-icon"></i>
                                                    @endif
                                                    {{ $log->user_name ?? 'System' }}
                                                </a>
                                            @else
                                                <span class="al-user-link" style="background:transparent;">
                                                    <i class="fa-solid fa-robot al-user-avatar-icon"></i>
                                                    {{ $log->user_name ?? 'System' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span
                                                style="font-size:11.5px;color:#94a3b8;">{{ $log->ip_address ?? '—' }}</span>
                                            <div style="font-size:10.5px;margin-top:3px;">
                                                {{ $log->created_at->format('d M Y, h:i A') }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="al-detail-btn" title="View details"
                                                data-when="{{ $log->created_at->format('d M Y, h:i:s A') }}"
                                                data-action="{{ $log->action }}" data-model="{{ $log->model_label }}"
                                                data-description="{{ $log->description }}"
                                                data-user="{{ $log->user_name ?? 'System' }}"
                                                data-ip="{{ $log->ip_address ?? '—' }}"
                                                data-properties="{{ json_encode($log->properties ?? []) }}">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3 border-top">
                        {{ $logs->links() }}
                    </div>
                @else
                    <div class="rol-empty">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <p>No activity recorded
                            {{ request()->anyFilled(['search', 'action', 'from', 'to', 'activity', 'type', 'user']) ? 'for this filter' : 'yet' }}.
                        </p>
                    </div>
                @endif
            </section>

        </div>
    </div>

    {{-- ── Column filter panels (outside table so clicks don't bubble into <th>) ──
         One shared GET form for all three panels: submitting from any of them
         applies whatever's checked across all three, as real query-string
         filters (activity[]/type[]/user[]). That's what makes them compose
         correctly with $logs->withQueryString() pagination — a client-side
         row toggle would only ever narrow the 25 rows already loaded. --}}
    <form method="GET" action="{{ route($routeName ?? 'activity-logs.index') }}">
        {{-- Mirror the filter bar's current selections so submitting from a
             panel doesn't wipe them out. --}}
        <input type="hidden" name="search" value="{{ request('search') }}">
        <input type="hidden" name="action" value="{{ request('action') }}">
        <input type="hidden" name="from" value="{{ request('from') }}">
        <input type="hidden" name="to" value="{{ request('to') }}">

        <div class="col-filter-panel" data-col-panel="action">
            <div class="cfp-header">Filter by Activity</div>
            <div class="cfp-body cfp-checkboxes">
                @foreach (['created', 'updated', 'deleted', 'restored', 'downloaded', 'login', 'logout', 'password_changed'] as $actionOption)
                    <label class="cfp-check-label">
                        <input type="checkbox" name="activity[]" value="{{ $actionOption }}"
                            {{ in_array($actionOption, (array) request('activity', [])) ? 'checked' : '' }}>
                        <span>{{ ucfirst(str_replace('_', ' ', $actionOption)) }}</span>
                    </label>
                @endforeach
            </div>
            <div class="cfp-footer">
                <a class="cfp-reset"
                    href="{{ request()->fullUrlWithQuery(['activity' => null, 'page' => null]) }}">Reset</a>
                <button type="submit" class="cfp-apply"><i class="fa-solid fa-check me-1"></i>Filter</button>
            </div>
        </div>

        <div class="col-filter-panel" data-col-panel="model">
            <div class="cfp-header">Filter by Type</div>
            <div class="cfp-body cfp-checkboxes">
                @foreach ($modelOptions as $modelOption)
                    @php
                        // UserAuthLog rows are labeled/filtered per-row as "Login"/"Logout"
                        // (see ActivityLog::getModelLabelAttribute()), not "UserAuthLog" —
                        // so this option has to match either of those, not its own literal name.
                        $optValue = $modelOption === 'UserAuthLog' ? 'login,logout' : strtolower($modelOption);
                    @endphp
                    <label class="cfp-check-label">
                        <input type="checkbox" name="type[]" value="{{ $optValue }}"
                            {{ in_array($optValue, (array) request('type', [])) ? 'checked' : '' }}>
                        <span>{{ $modelOption }}</span>
                    </label>
                @endforeach
            </div>
            <div class="cfp-footer">
                <a class="cfp-reset"
                    href="{{ request()->fullUrlWithQuery(['type' => null, 'page' => null]) }}">Reset</a>
                <button type="submit" class="cfp-apply"><i class="fa-solid fa-check me-1"></i>Filter</button>
            </div>
        </div>

        <div class="col-filter-panel cfp-right" data-col-panel="user">
            <div class="cfp-header">Filter by User</div>
            <div class="cfp-body cfp-checkboxes">
                @foreach ($userOptions as $userOption)
                    <label class="cfp-check-label">
                        <input type="checkbox" name="user[]" value="{{ strtolower($userOption) }}"
                            {{ in_array(strtolower($userOption), (array) request('user', [])) ? 'checked' : '' }}>
                        <span>{{ $userOption }}</span>
                    </label>
                @endforeach
            </div>
            <div class="cfp-footer">
                <a class="cfp-reset"
                    href="{{ request()->fullUrlWithQuery(['user' => null, 'page' => null]) }}">Reset</a>
                <button type="submit" class="cfp-apply"><i class="fa-solid fa-check me-1"></i>Filter</button>
            </div>
        </div>
    </form>

    {{-- ── Details modal (shared, populated on click) ── --}}
    <div class="modal fade rol-modal" id="logDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-clock-rotate-left me-2"></i>Activity Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="al-detail-meta" id="logDetailMeta"></div>
                    <div id="logDetailBody"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="rol-cancel-btn" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script>
        // Same mm/dd/yyyy display the native date input showed, but the
        // actual submitted value (name="from"/"to") stays Y-m-d — altInput
        // shows the formatted date to the user while keeping the real
        // input's value in the format the backend filter expects.
        document.querySelectorAll('[data-flatpickr]').forEach(function(el) {
            flatpickr(el, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'm/d/Y',
                altInputClass: el.className,
                allowInput: true,
            });
        });
    </script>
    <script>
        $(function() {
            var $modal = $('#logDetailModal');
            var $meta = $('#logDetailMeta');
            var $body = $('#logDetailBody');

            function escapeHtml(value) {
                return $('<div>').text(value).html();
            }

            function formatValue(value) {
                if (value === null || value === undefined || value === '') {
                    return '<em style="color:#94a3b8;">empty</em>';
                }
                if (typeof value === 'object') {
                    return escapeHtml(JSON.stringify(value));
                }
                return escapeHtml(String(value));
            }

            function formatBytes(bytes) {
                var n = Number(bytes);
                if (isNaN(n)) return formatValue(bytes);
                if (n < 1024) return n + ' B';
                var units = ['KB', 'MB', 'GB', 'TB'];
                var i = -1;
                do {
                    n /= 1024;
                    i++;
                } while (n >= 1024 && i < units.length - 1);
                return n.toFixed(n < 10 ? 2 : 1) + ' ' + units[i];
            }

            function formatFieldValue(key, value) {
                if (key === 'size' && value !== null && value !== undefined && value !== '') {
                    return formatBytes(value);
                }
                return formatValue(value);
            }

            function humanizeKey(key) {
                return key.replace(/_/g, ' ').replace(/\b\w/g, function(c) {
                    return c.toUpperCase();
                });
            }

            $(document).on('click', '.al-detail-btn', function() {
                var $btn = $(this);
                var action = $btn.data('action');
                var properties = {};
                try {
                    properties = JSON.parse($btn.attr('data-properties') || '{}');
                } catch (e) {
                    properties = {};
                }

                $meta.html(
                    '<span><strong>When:</strong> ' + escapeHtml($btn.data('when')) + '</span>' +
                    '<span><strong>Action:</strong> <span class="al-action-badge ' + escapeHtml(
                        action) + '">' + escapeHtml(action.replace(/_/g, ' ')) + '</span></span>' +
                    '<span><strong>User:</strong> ' + escapeHtml($btn.data('user')) + '</span>' +
                    '<span><strong>IP:</strong> ' + escapeHtml($btn.data('ip')) + '</span>'
                );

                var keys = Object.keys(properties);

                if (!keys.length) {
                    $body.html(
                        '<p style="color:#94a3b8;font-size:13px;margin:0;">No field-level details recorded for this entry.</p>'
                    );
                    $modal.modal('show');
                    return;
                }

                var isDiff = action === 'updated' || action === 'restored';
                var rows = '';

                keys.forEach(function(key) {
                    if ([
                            'id', 'user_id', 'parent_item_id'
                        ].indexOf(key) !== -1)
                        return; // internal/technical, already shown in the meta bar above

                    if (key === 'project_status') {
                        var delAt = properties['deleted_at'];
                        var isDeleted = (delAt !== null && typeof delAt === 'object' && ('old' in
                                delAt || 'new' in delAt)) ?
                            !!delAt.new :
                            !!delAt;
                        rows += '<tr><th>Status</th><td>' + (isDeleted ? 'Archive' : 'Active') +
                            '</td></tr>';
                        return;
                    }

                    var val = properties[key];
                    var label = escapeHtml(humanizeKey(key));
                    if (isDiff && val !== null && typeof val === 'object' && ('old' in val ||
                            'new' in val)) {
                        rows += '<tr><th>' + label + '</th><td>' +
                            '<span class="al-detail-old">' + formatFieldValue(key, val.old) +
                            '</span>' +
                            '<span class="al-detail-new">' + formatFieldValue(key, val.new) +
                            '</span>' +
                            '</td></tr>';
                    } else {
                        rows += '<tr><th>' + label + '</th><td>' + formatFieldValue(key, val) +
                            '</td></tr>';
                    }
                });

                if (!rows) {
                    $body.html(
                        '<p style="color:#94a3b8;font-size:13px;margin:0;">No field-level details recorded for this entry.</p>'
                    );
                    $modal.modal('show');
                    return;
                }

                $body.html('<table class="al-detail-table"><tbody>' + rows + '</tbody></table>');
                $modal.modal('show');
            });

            /* ── Column filter panels (Activity, Type, User) — open/close only;
               applying and resetting are real form submits/links (see the
               shared <form> around the panels), so the filter is part of the
               query string and paginates correctly. ── */
            $(document).on('click', '.col-filter-btn', function(e) {
                e.stopPropagation();
                var col = $(this).data('col');
                var panel = $('[data-col-panel="' + col + '"]');
                var isOpen = panel.hasClass('open');
                $('.col-filter-panel').removeClass('open');
                if (!isOpen) {
                    var rect = this.getBoundingClientRect();
                    var thRect = $(this).closest('th')[0].getBoundingClientRect();
                    var pw = 220;
                    var left = panel.hasClass('cfp-right') ? rect.right - pw : thRect.left;
                    if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
                    if (left < 8) left = 8;
                    panel.css({
                        top: rect.bottom + 4,
                        left: left
                    });
                    panel.addClass('open');
                }
            });

            $(document).on('click', '.col-filter-panel', function(e) {
                e.stopPropagation();
            });
            $(document).on('click', function() {
                $('.col-filter-panel').removeClass('open');
            });
            $(window).on('scroll resize', function() {
                $('.col-filter-panel').removeClass('open');
            });

            /* ── Column sort (Action, Model, User) ──
               3-click cycle per column: ascending → descending → default
               (original, server-provided order) → ascending → ... */
            var sortKey = '',
                sortDir = null;
            var activityLogsTbody = $('#activityLogsBody');
            var originalLogRowOrder = activityLogsTbody.find('tr').get();

            $(document).on('click', '.sortable', function() {
                var key = $(this).data('sort');

                if (sortKey !== key) {
                    sortDir = 'asc';
                } else if (sortDir === 'asc') {
                    sortDir = 'desc';
                } else if (sortDir === 'desc') {
                    sortDir = null;
                } else {
                    sortDir = 'asc';
                }
                sortKey = sortDir ? key : '';

                $('.sortable .sort-icon').attr('class', 'fa-solid fa-sort sort-icon');

                if (!sortDir) {
                    $.each(originalLogRowOrder, function(_, row) {
                        activityLogsTbody.append(row);
                    });
                    return;
                }

                var rows = activityLogsTbody.find('tr').get();

                rows.sort(function(a, b) {
                    var av = ($(a).data(key) || '').toString();
                    var bv = ($(b).data(key) || '').toString();
                    return sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
                });

                $.each(rows, function(_, row) {
                    activityLogsTbody.append(row);
                });

                $(this).find('.sort-icon').attr('class', 'fa-solid fa-sort-' + (sortDir === 'asc' ? 'up' :
                    'down') + ' sort-icon');
            });
        });
    </script>
@endpush
