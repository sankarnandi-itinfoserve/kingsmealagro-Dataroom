@extends('admin.layouts.app')

@section('title', 'Analytics — ' . $project->name)
@section('page_title', 'Project Analytics')

@section('content')

    @php
        $maxDay = $activityByDay->max('count') ?: 1;
        $chartDays = $activityByDay->values();
        $totalBars = max($chartDays->count(), 1);
    @endphp

    {{-- ── Page header ─────────────────────────────────────────────────────────── --}}
    <div class="an-header">
        <div class="an-header-body">
            <h5 class="an-title">{{ $project->name }}</h5>
            <nav class="an-breadcrumb">
                <a href="{{ route('analytics.index') }}">Analytics</a>
                <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
                <span>{{ Str::limit($project->name, 40) }}</span>
            </nav>
        </div>
        <div class="an-header-actions">
            <a href="{{ route('projects.edit', $project) }}" class="an-btn an-btn-ghost">
                <i class="fa-solid fa-eye"></i> View Project
            </a>
            <button type="button" class="an-btn an-btn-ghost" onclick="history.back()">
                <i class="fa-solid fa-arrow-left"></i> Back
            </button>
        </div>
    </div>

    {{-- ── Top Files + Top Users ────────────────────────────────────────────────── --}}
    <div class="row g-3">
        <div class="col-12 col-xl-6">
            <div class="an-card">
                <div class="an-card-header">
                    <span class="an-card-icon"><i class="fa-solid fa-file-circle-check"></i></span>
                    <span class="an-card-title">Top Files by Views</span>
                </div>
                <div class="an-card-body p-0">
                    @forelse ($topFiles as $i => $f)
                        @php
                            $fName = optional($f->folder)->name ?? '—';
                            $fId = optional($f->folder)->id;
                            $ext = strtolower(pathinfo($fName, PATHINFO_EXTENSION));
                            $icon = match (true) {
                                in_array($ext, ['doc', 'docx']) => ['fa-file-word', '#2563eb'],
                                in_array($ext, ['xls', 'xlsx']) => ['fa-file-excel', '#16a34a'],
                                in_array($ext, ['ppt', 'pptx']) => ['fa-file-powerpoint', '#ea580c'],
                                $ext === 'pdf' => ['fa-file-pdf', '#dc2626'],
                                in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) => ['fa-file-image', '#7c3aed'],
                                default => ['fa-file', '#64748b'],
                            };
                            $fBreadcrumb = $fId ? collect($f->folder->getBreadcrumb())->slice(0, -1)->values() : collect();
                        @endphp
                        <div class="an-list-row">
                            <span class="an-rank">{{ $i + 1 }}</span>
                            <i class="fa-solid {{ $icon[0] }}"
                                style="color:{{ $icon[1] }};font-size:15px;flex-shrink:0;"></i>
                            <div class="an-list-meta overflow-hidden an-flex1">
                                @if ($fId)
                                    <a href="{{ route('files.preview', base64_encode($fId)) }}"
                                        class="an-list-name an-doc-name-link text-truncate d-block"
                                        title="{{ $fName }}">{{ $fName }}</a>
                                @else
                                    <div class="an-list-name text-truncate" title="{{ $fName }}">{{ $fName }}</div>
                                @endif
                                @if ($fBreadcrumb->isNotEmpty())
                                    <div class="an-list-sub text-truncate">
                                        @foreach ($fBreadcrumb as $j => $folder)
                                            @if ($j > 0)<span class="an-doc-path-sep">/</span>@endif
                                            <a href="{{ route('shared.folders') }}#path={{ $fBreadcrumb->slice(0, $j + 1)->pluck('id')->implode(',') }}"
                                                class="an-doc-path-link" target="_blank"
                                                rel="noopener">{{ $folder->name }}</a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <span class="an-badge an-badge-solid-green ms-auto flex-shrink-0">
                                <i class="fa-solid fa-eye me-1"></i>{{ number_format($f->total_views) }}
                            </span>
                        </div>
                    @empty
                        <div class="an-empty"><i class="fa-regular fa-file"></i><span>No file views recorded</span></div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-6">
            <div class="an-card">
                <div class="an-card-header">
                    <span class="an-card-icon"><i class="fa-solid fa-ranking-star"></i></span>
                    <span class="an-card-title">Top Users by Activity</span>
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
                                <img class="an-avatar" src="{{ asset('storage/' . $u->user->avatar) }}" style="object-fit:cover;" alt="">
                            @else
                                <i class="fa-solid fa-circle-user" style="font-size:30px;color:#94a3b8;flex-shrink:0;line-height:1;"></i>
                            @endif
                            <div class="an-list-meta overflow-hidden">
                                @if ($uid)
                                    <a href="{{ route('analytics.userDetail', $uid) }}"
                                        class="an-list-name an-doc-name-link text-truncate d-block">{{ $uName }}</a>
                                @else
                                    <div class="an-list-name text-truncate">{{ $uName }}</div>
                                @endif
                                @if (optional($u->user)->email)
                                    <div class="an-list-sub text-truncate">{{ $u->user->email }}</div>
                                @endif
                            </div>
                            <div class="ms-auto d-flex align-items-center gap-2 flex-shrink-0">
                                <span class="an-badge an-badge-solid-green">{{ number_format($u->activity_count) }}
                                    views</span>
                            </div>
                        </div>
                    @empty
                        <div class="an-empty"><i class="fa-regular fa-user"></i><span>No user activity recorded</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Activity logs (this project only) ───────────────────────────────────── --}}
    <div class="an-card mt-3">
        <div class="an-card-header">
            <span class="an-card-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
            <span class="an-card-title">Activity Logs</span>
        </div>
        <div class="an-card-body p-0">
            @if ($projectLogs->count())
                <div class="table-responsive">
                    <table class="table rol-table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>When</th>
                                <th class="col-has-filter">
                                    <div class="col-th-inner">
                                        <span>Activity</span>
                                        <button type="button"
                                            class="col-filter-btn {{ request()->filled('activity') ? 'active' : '' }}"
                                            data-col="activity" title="Filter"><i
                                                class="fa-solid fa-filter"></i></button>
                                    </div>
                                </th>
                                <th class="col-has-filter">
                                    <div class="col-th-inner">
                                        <span>Type</span>
                                        <button type="button"
                                            class="col-filter-btn {{ request()->filled('type') ? 'active' : '' }}"
                                            data-col="type" title="Filter"><i
                                                class="fa-solid fa-filter"></i></button>
                                    </div>
                                </th>
                                <th>Description</th>
                                <th class="col-has-filter">
                                    <div class="col-th-inner">
                                        <span>User</span>
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
                        <tbody>
                            @foreach ($projectLogs as $log)
                                <tr>
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
                                        <span style="font-size:13px;color:#334155;">{!! $logDescriptions[$log->id] ?? e($log->description) !!}</span>
                                        @if (!empty($logPaths[$log->id]))
                                            <div style="font-size:11px;color:#64758d;margin-top:3px;">
                                                <i class="fa-solid fa-angle-right"
                                                    style="font-size:9px;margin-right:3px;"></i>{{ $logPaths[$log->id] }}
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
                <div class="px-3 py-3 border-top">
                    {{ $projectLogs->links() }}
                </div>
            @else
                <div class="rol-empty">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <p>No activity recorded for this project yet.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Column filter panels (outside the table so clicks don't bubble into <th>) ──
         One shared GET form for all three panels: submitting from any of them applies
         whatever's checked across all three, as real query-string filters
         (activity[]/type[]/user[]) — that's what makes them compose correctly with
         $projectLogs->withQueryString() pagination, unlike a client-side row toggle
         which would only ever narrow the rows already loaded on the current page. --}}
    <form method="GET" action="{{ route('analytics.project', $project) }}">
        <div class="col-filter-panel" data-col-panel="activity">
            <div class="cfp-header">Filter by Activity</div>
            <div class="cfp-body cfp-checkboxes">
                @foreach ($activityOptions as $activityOption)
                    <label class="cfp-check-label">
                        <input type="checkbox" name="activity[]" value="{{ $activityOption }}"
                            {{ in_array($activityOption, (array) request('activity', [])) ? 'checked' : '' }}>
                        <span>{{ ucfirst(str_replace('_', ' ', $activityOption)) }}</span>
                    </label>
                @endforeach
            </div>
            <div class="cfp-footer">
                <a class="cfp-reset"
                    href="{{ request()->fullUrlWithQuery(['activity' => null, 'logsPage' => null]) }}">Reset</a>
                <button type="submit" class="cfp-apply"><i class="fa-solid fa-check me-1"></i>Filter</button>
            </div>
        </div>

        <div class="col-filter-panel" data-col-panel="type">
            <div class="cfp-header">Filter by Type</div>
            <div class="cfp-body cfp-checkboxes">
                @foreach ($modelOptions as $modelOption)
                    <label class="cfp-check-label">
                        <input type="checkbox" name="type[]" value="{{ strtolower($modelOption) }}"
                            {{ in_array(strtolower($modelOption), (array) request('type', [])) ? 'checked' : '' }}>
                        <span>{{ $modelOption }}</span>
                    </label>
                @endforeach
            </div>
            <div class="cfp-footer">
                <a class="cfp-reset"
                    href="{{ request()->fullUrlWithQuery(['type' => null, 'logsPage' => null]) }}">Reset</a>
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
                    href="{{ request()->fullUrlWithQuery(['user' => null, 'logsPage' => null]) }}">Reset</a>
                <button type="submit" class="cfp-apply"><i class="fa-solid fa-check me-1"></i>Filter</button>
            </div>
        </div>
    </form>

    {{-- ── Activity chart ───────────────────────────────────────────────────────── --}}
    <div class="an-card mt-3">
        <div class="an-card-header">
            <span class="an-card-icon"><i class="fa-solid fa-chart-bar"></i></span>
            <span class="an-card-title">Document View Activity — Last 30 Days</span>
        </div>
        <div class="an-card-body" style="padding:20px 20px 12px;display:flex;flex-direction:column;">
            @if ($chartDays->isNotEmpty())
                <div class="an-bar-chart">
                    @foreach ($chartDays as $day)
                        @php
                            $pct = round(($day->count / $maxDay) * 100);
                            $dt = \Carbon\Carbon::parse($day->date);
                        @endphp
                        <div class="an-bar-col" title="{{ $dt->format('D d M') }}: {{ number_format($day->count) }} views">
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
                <div class="an-empty"><i class="fa-solid fa-chart-bar"></i><span>No view activity in the last 30 days</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Log details modal (shared, populated on click) ── --}}
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
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .an-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        .an-breadcrumb a:hover {
            color: #253447;
        }

        .an-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .an-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
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

        .an-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        .an-card-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            background: #253447;
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

        .an-list-meta {
            flex: 1;
            min-width: 0;
        }

        .an-list-name {
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
        }

        .an-list-sub {
            font-size: 11.5px;
            color: #94a3b8;
        }

        .an-flex1 {
            flex: 1;
            min-width: 0;
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

        .an-doc-path-link {
            color: inherit;
            text-decoration: none;
        }

        .an-doc-path-link:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        .an-doc-path-sep {
            margin: 0 4px;
            color: #cbd5e1;
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

        @media (max-width: 768px) {
            .an-header {
                flex-wrap: wrap;
            }

            .an-header-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }

        /* ── Activity logs table (same look as the main Activity Logs page) ── */
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

        /* ── Column filter (Activity, Type, User) ──────────────────────────── */
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
    </style>
@endpush

@push('script')
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
        });
    </script>
@endpush
