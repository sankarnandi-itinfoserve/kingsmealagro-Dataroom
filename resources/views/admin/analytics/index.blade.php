@extends('admin.layouts.app')

@section('title', 'Analytics')
@section('page_title', 'Analytics')

@section('content')

    @php
        $maxDay = $activityByDay->max('count') ?: 1;
        $chartDays = $activityByDay->values();
        $totalBars = max($chartDays->count(), 1);

    @endphp

    {{-- ── Page header ─────────────────────────────────────────────────────────── --}}
    {{-- <div class="an-header">
        <div class="an-header-icon"><i class="fa-solid fa-chart-line"></i></div>
        <div class="an-header-body">
            <h5 class="an-title">Analytics</h5>
            <nav class="an-breadcrumb"><span>Overview — All Projects</span></nav>
        </div>
        <div class="an-header-actions">
            <a href="{{ route('analytics.index', ['export' => 'csv']) }}" class="an-btn an-btn-ghost">
                <i class="fa-solid fa-file-csv"></i> Export CSV
            </a>
            <button onclick="window.print()" class="an-btn an-btn-ghost">
                <i class="fa-solid fa-print"></i> Print / PDF
            </button>
        </div>
    </div> --}}

    {{-- ── Stat cards ───────────────────────────────────────────────────────────── --}}
    {{-- <div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="an-stat-card">
            <div class="an-stat-icon" style="background:#eef2ff;color:#4f46e5;">
                <i class="fa-solid fa-right-to-bracket"></i>
            </div>
            <div class="an-stat-body">
                <div class="an-stat-val">{{ number_format($totalLogins) }}</div>
                <div class="an-stat-label">Total Logins</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="an-stat-card">
            <div class="an-stat-icon" style="background:#f0fdf4;color:#16a34a;">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="an-stat-body">
                <div class="an-stat-val">{{ number_format($uniqueUsers) }}</div>
                <div class="an-stat-label">Unique Users</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="an-stat-card">
            <div class="an-stat-icon" style="background:#fff7ed;color:#ea580c;">
                <i class="fa-solid fa-eye"></i>
            </div>
            <div class="an-stat-body">
                <div class="an-stat-val">{{ number_format($totalViews) }}</div>
                <div class="an-stat-label">Document Views</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="an-stat-card">
            <div class="an-stat-icon" style="background:#f0f9ff;color:#0284c7;">
                <i class="fa-solid fa-diagram-project"></i>
            </div>
            <div class="an-stat-body">
                <div class="an-stat-val">{{ number_format($activeProjects) }}</div>
                <div class="an-stat-label">Active Projects</div>
            </div>
        </div>
    </div>
</div> --}}

    {{-- ── Activity chart + Project list ───────────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8 d-flex">
            <div class="an-card h-100 w-100">
                <div class="an-card-header">
                    <span class="an-card-icon"><i class="fa-solid fa-chart-bar"></i></span>
                    <span class="an-card-title">Login Activity — Last 30 Days</span>
                </div>
                <div class="an-card-body" style="padding:20px 20px 12px;display:flex;flex-direction:column;">
                    @if ($chartDays->isNotEmpty())
                        <div class="an-bar-chart">
                            @foreach ($chartDays as $day)
                                @php
                                    $pct = round(($day->count / $maxDay) * 100);
                                    $dt = \Carbon\Carbon::parse($day->date);
                                @endphp
                                <div class="an-bar-col"
                                    title="{{ $dt->format('D d M') }}: {{ number_format($day->count) }} logins">
                                    <div class="an-bar" style="height:{{ max($pct, 3) }}%;">
                                        @if ($day->count > 0)
                                            <span class="an-bar-count">{{ number_format($day->count) }}</span>
                                        @endif
                                    </div>
                                    @if ($loop->iteration % 5 === 1 || $loop->last)
                                        <div class="an-bar-label">{{ $dt->format('d M') }}</div>
                                    @else
                                        <div class="an-bar-label"></div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="an-empty"><i class="fa-solid fa-chart-bar"></i><span>No login activity in the last 30
                                days</span></div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4 d-flex">
            <div class="an-card h-100 w-100">
                <div class="an-card-header">
                    <span class="an-card-icon"><i class="fa-solid fa-diagram-project"></i></span>
                    <span class="an-card-title">Project Analytics</span>
                    <div class="an-proj-search-wrap ms-auto">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="projSearchInput" placeholder="Search…" autocomplete="off">
                    </div>
                </div>
                <div class="an-card-body p-0 an-proj-list">
                    @forelse ($projects as $proj)
                        @php
                            $b = (int) ($proj->total_size ?? 0);
                            $projSize =
                                $b >= 1073741824
                                    ? number_format($b / 1073741824, 1) . ' GB'
                                    : ($b >= 1048576
                                        ? number_format($b / 1048576, 1) . ' MB'
                                        : ($b >= 1024
                                            ? number_format($b / 1024, 0) . ' KB'
                                            : $b . ' B'));
                            $projModified = $proj->updated_at ? $proj->updated_at->format('d M Y') : '—';
                        @endphp
                        <a href="{{ route('analytics.project', $proj) }}" class="an-proj-row"
                            data-name="{{ strtolower($proj->name) }}">
                            <div class="an-proj-folder-icon">
                                <i class="fa-solid fa-folder"></i>
                            </div>
                            <div class="an-proj-info">
                                <span class="an-proj-name">{{ $proj->name }}</span>
                            </div>
                            <span class="an-proj-size">{{ $projSize }}</span>
                            <i class="fa-solid fa-chevron-right an-proj-arrow"></i>
                        </a>
                    @empty
                        <div class="an-empty"><i class="fa-solid fa-diagram-project"></i><span>No projects</span></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Top Users + Top Documents ────────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-6">
            <div class="an-card">
                <div class="an-card-header">
                    <span class="an-card-icon"><i class="fa-solid fa-ranking-star"></i></span>
                    <span class="an-card-title">Top Users by Logins</span>
                </div>
                <div class="an-card-body p-0">
                    @forelse ($topUsers as $i => $u)
                        @php
                            $uName = optional($u->user)->fname
                                ? trim(optional($u->user)->fname . ' ' . optional($u->user)->lname)
                                : optional($u->user)->email ?? '—';
                            $uid = optional($u->user)->id;
                        @endphp
                        <div class="an-list-row">
                            <span class="an-rank">{{ $i + 1 }}</span>
                            @if (optional($u->user)->avatar)
                                <img class="an-avatar" src="{{ asset('storage/' . $u->user->avatar) }}"
                                    style="object-fit:cover;" alt="">
                            @else
                                <i class="fa-solid fa-circle-user"
                                    style="font-size:30px;color:#94a3b8;flex-shrink:0;line-height:1;"></i>
                            @endif
                            <div class="an-list-meta overflow-hidden">
                                <div class="an-list-name text-truncate">
                                    @if ($uid)
                                        <a href="{{ route('analytics.userDetail', $uid) }}" class="an-doc-name-link"
                                            title="{{ $uName }}">{{ $uName }}</a>
                                    @else
                                        {{ $uName }}
                                    @endif
                                </div>
                                @if (optional($u->user)->email)
                                    <div class="an-list-sub text-truncate">{{ $u->user->email }}</div>
                                @endif
                            </div>
                            <div class="ms-auto d-flex align-items-center gap-2 flex-shrink-0">
                                <span
                                    class="an-badge an-badge-solid-green an-badge-fixed-w">{{ number_format($u->login_count) }}
                                    logins</span>
                            </div>
                        </div>
                    @empty
                        <div class="an-empty"><i class="fa-regular fa-user"></i><span>No login data</span></div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="an-card">
                <div class="an-card-header">
                    <span class="an-card-icon"><i class="fa-solid fa-file-circle-check"></i></span>
                    <span class="an-card-title">Top Documents by Views</span>
                </div>
                <div class="an-card-body p-0">
                    @forelse ($topDocuments as $i => $d)
                        @php
                            $dName = optional($d->folder)->name ?? '—';
                            $dSize = optional($d->folder)->size;
                            $dSizeFmt =
                                $dSize !== null
                                    ? ($dSize >= 1048576
                                        ? number_format($dSize / 1048576, 1) . ' MB'
                                        : ($dSize >= 1024
                                            ? number_format($dSize / 1024, 0) . ' KB'
                                            : $dSize . ' B'))
                                    : null;
                            $dId = optional($d->folder)->id;
                            $ext = strtolower(pathinfo($dName, PATHINFO_EXTENSION));
                            $icon = match (true) {
                                in_array($ext, ['doc', 'docx']) => ['fa-file-word', '#2563eb'],
                                in_array($ext, ['xls', 'xlsx']) => ['fa-file-excel', '#16a34a'],
                                in_array($ext, ['ppt', 'pptx']) => ['fa-file-powerpoint', '#ea580c'],
                                $ext === 'pdf' => ['fa-file-pdf', '#dc2626'],
                                default => ['fa-file', '#64748b'],
                            };
                            $dLocation = $d->folder
                                ? collect($d->folder->getBreadcrumb())
                                    ->pluck('name')
                                    ->slice(0, -1)
                                    ->implode(' / ')
                                : null;
                        @endphp
                        <div class="an-list-row">
                            <span class="an-rank">{{ $i + 1 }}</span>
                            <i class="fa-solid {{ $icon[0] }}"
                                style="color:{{ $icon[1] }};font-size:30px;flex-shrink:0;line-height:1;"></i>
                            <div class="an-list-meta overflow-hidden">
                                <div class="an-list-name text-truncate">
                                    @if ($dId)
                                        <a href="{{ route('files.preview', base64_encode($dId)) }}"
                                            class="an-doc-name-link"
                                            title="{{ $dName }}">{{ $dName }}</a>
                                    @else
                                        {{ $dName }}
                                    @endif
                                    @if ($dSizeFmt)
                                        <span style="font-size:10.5px;color:#94a3b8;font-weight:400;"> ·
                                            {{ $dSizeFmt }}</span>
                                    @endif
                                </div>
                                @if ($dLocation)
                                    <div class="an-list-sub text-truncate" title="{{ $dLocation }}">{{ $dLocation }}
                                    </div>
                                @endif
                            </div>
                            <span class="an-badge an-badge-solid-green flex-shrink-0">
                                <i class="fa-solid fa-eye me-1"></i>{{ number_format($d->total_views) }}
                            </span>
                        </div>
                    @empty
                        <div class="an-empty"><i class="fa-regular fa-file"></i><span>No view data</span></div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Activity ──────────────────────────────────────────────────────── --}}
    <div class="an-card">
        <div class="an-card-header">
            <span class="an-card-icon"><i class="fa-solid fa-list-check"></i></span>
            <span class="an-card-title">Recent Login Activity</span>
        </div>
        <div class="an-card-body p-0">
            <div class="table-responsive">
                <table class="table an-table mb-0">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th class="col-has-filter">
                                <div class="col-th-inner">
                                    <span>Type</span>
                                    <button type="button" class="col-filter-btn" data-col="type" title="Filter"><i
                                            class="fa-solid fa-filter"></i></button>
                                </div>
                            </th>
                            <th>Device</th>
                            <th class="text-end">Time</th>
                        </tr>
                    </thead>
                    <tbody id="recentActivityBody">
                        @forelse ($recentActivity as $log)
                            @php
                                $lName = optional($log->user)->fname
                                    ? trim(optional($log->user)->fname . ' ' . optional($log->user)->lname)
                                    : optional($log->user)->email ?? '—';
                                $lt = strtolower($log->logon_type ?? '');
                                $typeLabel = match ($lt) {
                                    'logout' => 'Logout',
                                    default => 'Login',
                                };
                                $lId = optional($log->user)->id;
                            @endphp
                            <tr data-type="{{ strtolower($typeLabel) }}">
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if (optional($log->user)->avatar)
                                            <img class="an-avatar an-avatar-sm"
                                                src="{{ asset('storage/' . $log->user->avatar) }}"
                                                style="object-fit:cover;" alt="">
                                        @else
                                            <i class="fa-solid fa-circle-user"
                                                style="font-size:26px;color:#94a3b8;flex-shrink:0;line-height:1;"></i>
                                        @endif
                                        <div>
                                            <div class="an-list-name">
                                                @if ($lId)
                                                    <a href="{{ route('analytics.userDetail', $lId) }}"
                                                        class="an-doc-name-link" title="{{ $lName }}">{{ $lName }}</a>
                                                @else
                                                    {{ $lName }}
                                                @endif
                                            </div>
                                            @if (optional($log->user)->email)
                                                <div class="an-list-sub">{{ $log->user->email }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td><span class="an-badge an-badge-solid-green an-badge-fixed-w">{{ $typeLabel }}</span></td>
                                <td class="text-muted" style="font-size:12.5px;">
                                    {{ $log->device_info ?? '—' }}</td>
                                <td class="text-end text-muted" style="font-size:12.5px;white-space:nowrap;">
                                    {{ $log->logged_in ? \Carbon\Carbon::parse($log->logged_in)->format('d M Y, H:i') : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">No recent activity.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ── Column filter panel (outside table so clicks don't bubble into <th>) ── --}}
    <div class="col-filter-panel" data-col-panel="type">
        <div class="cfp-header">Filter by Type</div>
        <div class="cfp-body cfp-checkboxes">
            @foreach (['login' => 'Login', 'logout' => 'Logout'] as $typeValue => $typeOptionLabel)
                <label class="cfp-check-label">
                    <input type="checkbox" value="{{ $typeValue }}">
                    <span>{{ $typeOptionLabel }}</span>
                </label>
            @endforeach
        </div>
        <div class="cfp-footer">
            <button type="button" class="cfp-reset" data-col="type">Reset</button>
            <button type="button" class="cfp-apply" data-col="type"><i
                    class="fa-solid fa-check me-1"></i>Filter</button>
        </div>
    </div>

@endsection

@push('addOnCss')
    <style>
        @media print {

            .sidebar,
            header,
            .an-header-actions {
                display: none !important;
            }

            .an-card {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                page-break-inside: avoid;
            }
        }

        .an-header {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 16px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .07);
        }

        .an-header-icon {
            width: 46px;
            height: 46px;
            border-radius: 11px;
            background: #253447;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            flex-shrink: 0;
        }

        .an-header-body {
            flex: 1;
            min-width: 0;
        }

        .an-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 3px;
        }

        .an-breadcrumb {
            font-size: 12px;
            color: #94a3b8;
        }

        .an-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .an-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid;
            cursor: pointer;
            background: none;
            transition: background .13s, color .13s;
        }

        .an-btn-ghost {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #475569;
        }

        .an-btn-ghost:hover {
            background: #253447;
            border-color: #253447;
            color: #fff;
        }

        .an-stat-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            border: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .an-stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .an-stat-val {
            font-size: 22px;
            font-weight: 800;
            color: #1e293b;
            font-variant-numeric: tabular-nums;
            line-height: 1;
        }

        .an-stat-label {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
            margin-top: 3px;
        }

        .an-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            border: 1px solid #f1f5f9;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .an-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 18px;
            height: 48px;
            background: #253447;
            box-sizing: border-box;
        }

        .an-proj-search-wrap {
            display: flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 5px;
            padding: 3px 8px;
        }

        .an-proj-search-wrap i {
            color: rgba(255, 255, 255, .55);
            font-size: 10px;
        }

        .an-proj-search-wrap input {
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-size: 11.5px;
            width: 100px;
            line-height: 1;
            padding: 0;
        }

        .an-proj-search-wrap input::placeholder {
            color: rgba(255, 255, 255, .45);
        }

        .an-card-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .an-card-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
        }

        .an-card-body {
            min-height: 60px;
            flex: 1;
        }

        .an-bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            height: 220px;
            padding-top: 36px;
            padding-bottom: 30px;
            position: relative;
            background: #e6f7fa;
            border-radius: 10px;
            border: 1px solid #a8dee6;
        }

        /* Horizontal grid lines */
        .an-bar-chart::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 36px;
            bottom: 30px;
            background: repeating-linear-gradient(to bottom,
                    transparent 0,
                    transparent calc(33.33% - 1px),
                    #cdeef3 calc(33.33% - 1px),
                    #cdeef3 33.33%);
            border-radius: 8px;
            pointer-events: none;
            z-index: 0;
        }

        .an-bar-col {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            height: 100%;
            position: relative;
            z-index: 1;
        }

        .an-bar {
            width: 50%;
            background: #0d9488;
            border-radius: 5px 5px 0 0;
            min-height: 4px;
            transition: transform .18s ease, filter .18s ease, box-shadow .18s ease;
            position: relative;
            box-shadow: 0 3px 10px rgba(13, 148, 136, .25);
        }

        .an-bar-col:hover .an-bar {
            filter: brightness(1.1);
            transform: scaleY(1.03);
            transform-origin: bottom;
            box-shadow: 0 6px 16px rgba(13, 148, 136, .40);
        }

        .an-bar-count {
            position: absolute;
            top: -24px;
            left: 50%;
            transform: translateX(-50%);
            background: #0d9488;
            color: #fff;
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 6px;
            border-radius: 5px;
            white-space: nowrap;
            box-shadow: 0 2px 6px rgba(13, 148, 136, .30);
            pointer-events: none;
        }

        .an-bar-count::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 4px solid transparent;
            border-top-color: #0d9488;
        }

        .an-bar-label {
            font-size: 9.5px;
            color: #000;
            white-space: nowrap;
            margin-top: 4px;
            font-weight: 500;
        }

        .an-proj-list {
            max-height: 290px;
            overflow-y: auto;
        }

        .an-proj-list::-webkit-scrollbar {
            width: 4px;
        }

        .an-proj-list::-webkit-scrollbar-track {
            background: transparent;
        }

        .an-proj-list::-webkit-scrollbar-thumb {
            background: rgba(37, 52, 71, 0.25);
            border-radius: 4px;
        }

        .an-proj-row {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 2px 16px;
            border-bottom: 1px solid #f1f5f9;
            text-decoration: none;
            color: #1e293b;
        }

        .an-proj-row:hover {}

        .an-proj-row:last-child {
            border-bottom: none;
        }

        .an-proj-folder-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
            color: #f59e0b;
        }

        .an-proj-info {
            flex: 1;
            min-width: 0;
        }

        .an-proj-name {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .an-proj-size {
            font-size: 11.5px;
            font-weight: 500;
            color: #64748b;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .an-proj-arrow {
            font-size: 10px;
            color: #cbd5e1;
            flex-shrink: 0;
        }

        .an-flex1 {
            flex: 1;
            min-width: 0;
        }

        .an-list-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 18px;
            border-bottom: 1px solid #f8fafc;
            transition: background .1s;
        }

        .an-list-row:last-child {
            border-bottom: none;
        }

        .an-list-row:hover {
            background: #f8fafc;
        }

        .an-rank {
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .an-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #253447;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .an-avatar-sm {
            width: 26px;
            height: 26px;
            font-size: 11px;
        }

        .an-list-meta {
            flex: 1;
            min-width: 0;
        }

        .an-list-name {
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
        }

        .an-doc-name-link {
            color: #1e293b;
            text-decoration: underline;
            text-decoration-color: transparent;
            transition: text-decoration-color .15s;
        }

        .an-doc-name-link:hover {
            color: #253447;
            text-decoration-color: currentColor;
        }

        .an-list-sub {
            font-size: 11.5px;
            color: #94a3b8;
        }

        .an-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .an-badge-solid-green {
            background: #0d9488;
            color: #fff;
        }

        .an-badge-fixed-w {
            width: 92px;
            justify-content: center;
        }

        .an-table thead th {
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #64748b;
            background: #f8fafc;
            padding: 11px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .an-table tbody td {
            padding: 11px 16px;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }

        .an-table tbody tr:last-child td {
            border-bottom: none;
        }

        .an-table tbody tr:hover td {
            background: #f8fafc;
        }

        .an-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 36px 20px;
            color: #94a3b8;
            font-size: 13px;
        }

        .an-empty i {
            font-size: 22px;
            opacity: .35;
        }

        /* ── Column filter (Type) ── */
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

        @media (max-width: 768px) {
            .an-header {
                flex-wrap: wrap;
            }

            .an-header-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
@endpush

@push('script')
    <script>
        $('#projSearchInput').on('input', function() {
            const q = $(this).val().toLowerCase().trim();
            $('.an-proj-row').each(function() {
                $(this).toggle(!q || $(this).data('name').includes(q));
            });
        });

        /* ── Column filter (Type) — scoped to the current page's rows,
           same pattern as the Activity Logs page ── */
        var colFilters = {
            type: []
        };

        function applyColFilters() {
            $('#recentActivityBody tr').each(function() {
                var $row = $(this);
                var matchType = !colFilters.type.length || colFilters.type.indexOf($row.data(
                    'type')) !== -1;
                $row.toggle(matchType);
            });
        }

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

        $(document).on('click', '.cfp-apply', function() {
            var col = $(this).data('col');
            var panel = $('[data-col-panel="' + col + '"]');
            colFilters[col] = panel.find('input[type="checkbox"]:checked').map(function() {
                return $(this).val();
            }).get();
            $('[data-col="' + col + '"].col-filter-btn').toggleClass('active', colFilters[col].length > 0);
            panel.removeClass('open');
            applyColFilters();
        });

        $(document).on('click', '.cfp-reset', function() {
            var col = $(this).data('col');
            var panel = $('[data-col-panel="' + col + '"]');
            panel.find('input[type="checkbox"]').prop('checked', false);
            colFilters[col] = [];
            panel.removeClass('open');
            $('[data-col="' + col + '"].col-filter-btn').removeClass('active');
            applyColFilters();
        });
    </script>
@endpush
