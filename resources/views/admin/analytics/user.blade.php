@extends('admin.layouts.app')

@section('title', 'Analytics — ' . ($user->fname ? trim($user->fname . ' ' . $user->lname) : $user->email))
@section('page_title', 'User Analytics')

@section('content')

    @php
        $displayName = $user->fname
            ? trim($user->fname . ' ' . $user->lname)
            : $user->displayName ?? ($user->username ?? ($user->email ?? '—'));
        $maxDay = $activityByDay->max('count') ?: 1;
        $chartDays = $activityByDay->values();
    @endphp

    {{-- ── Page header ─────────────────────────────────────────────────────────── --}}
    <div class="an-header">
        @if ($user->avatar)
            <img src="{{ asset('storage/' . $user->avatar) }}" class="an-avatar an-avatar-lg" style="object-fit:cover;"
                alt="{{ $displayName }}">
        @else
            <i class="fa-solid fa-circle-user" style="font-size:46px;color:#94a3b8;flex-shrink:0;line-height:1;"></i>
        @endif
        <div class="an-header-body">
            <h5 class="an-title">{{ $displayName }}</h5>
            <nav class="an-breadcrumb">
                <a href="{{ route('analytics.index') }}">Analytics</a>
                <i class="fa-solid fa-chevron-right" style="font-size:9px;"></i>
                <span>{{ $displayName }}</span>
            </nav>
        </div>
        <div class="an-header-actions">
            <a href="{{ route('analytics.index') }}" class="an-btn an-btn-ghost">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    {{-- ── Stat cards ───────────────────────────────────────────────────────────── --}}
    {{-- <div class="row g-3 mb-3">
        <div class="col-6 col-xl-4">
            <div class="an-stat-card">
                <div class="an-stat-icon" style="background:rgba(37,52,71,.08);color:#253447;">
                    <i class="fa-solid fa-right-to-bracket"></i>
                </div>
                <div class="an-stat-body">
                    <div class="an-stat-val">{{ number_format($totalLogins) }}</div>
                    <div class="an-stat-label">Total Logins</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="an-stat-card">
                <div class="an-stat-icon" style="background:rgba(37,52,71,.08);color:#253447;">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <div class="an-stat-body">
                    <div class="an-stat-val">{{ number_format($totalViews) }}</div>
                    <div class="an-stat-label">Document Views</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="an-stat-card">
                <div class="an-stat-icon" style="background:rgba(37,52,71,.08);color:#253447;">
                    <i class="fa-solid fa-clock"></i>
                </div>
                <div class="an-stat-body">
                    <div class="an-stat-val" style="font-size:15px;">
                        {{ $lastSeen ? \Carbon\Carbon::parse($lastSeen)->format('d M Y') : '—' }}
                    </div>
                    <div class="an-stat-label">Last Seen</div>
                </div>
            </div>
        </div>
    </div> --}}

    {{-- ── User info + Activity chart ───────────────────────────────────────────── --}}
    <div class="row g-3 mb-3">

        {{-- User info card --}}
        <div class="col-12 col-xl-4">
            <div class="an-card h-100">
                <div class="an-card-header">
                    <span class="an-card-icon"><i class="fa-solid fa-user"></i></span>
                    <span class="an-card-title">User Details</span>
                </div>
                <div class="an-card-body" style="padding:20px;">
                    <div class="an-user-profile-block">
                        @if ($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="an-user-profile-avatar" alt="{{ $displayName }}">
                        @else
                            <i class="fa-solid fa-circle-user an-user-profile-avatar-icon"></i>
                        @endif
                        <div class="an-user-profile-name">{{ $displayName }}</div>
                    </div>
                    <div class="an-user-info-row">
                        <span class="an-info-label">Email</span>
                        <span class="an-info-val">{{ $user->email ?? '—' }}</span>
                    </div>
                    @if ($user->job_title)
                        <div class="an-user-info-row">
                            <span class="an-info-label">Job Title</span>
                            <span class="an-info-val">{{ $user->job_title }}</span>
                        </div>
                    @endif
                    @if ($user->division)
                        <div class="an-user-info-row">
                            <span class="an-info-label">Division</span>
                            <span class="an-info-val">{{ $user->division }}</span>
                        </div>
                    @endif
                    <div class="an-user-info-row">
                        <span class="an-info-label">Role</span>
                        <span class="an-info-val">{{ $user->getRoleNames()->first() ? Str::title(str_replace('-', ' ', $user->getRoleNames()->first())) : '—' }}</span>
                    </div>
                    <div class="an-user-info-row">
                        <span class="an-info-label">Status</span>
                        <span class="an-info-val">
                            @if (is_null($user->deleted_at))
                                <span class="an-badge an-badge-solid-green">Active</span>
                            @else
                                <span class="an-badge an-badge-grey">Inactive</span>
                            @endif
                        </span>
                    </div>
                    <div class="an-user-info-row">
                        <span class="an-info-label">Last Login</span>
                        <span class="an-info-val">{{ $lastSeen ? \Carbon\Carbon::parse($lastSeen)->format('d M Y, h:i A') : '—' }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Activity by day --}}
        <div class="col-12 col-xl-8">
            <div class="an-card h-100">
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

    </div>

    {{-- ── Login history + Document access ────────────────────────────────────── --}}
    <div class="row g-3">

        {{-- <div class="col-12 col-xl-6">
        <div class="an-card">
            <div class="an-card-header">
                <span class="an-card-icon"><i class="fa-solid fa-list-check"></i></span>
                <span class="an-card-title">Login History</span>
                <span class="an-card-badge ms-auto">Last 50</span>
            </div>
            <div class="an-card-body p-0" style="max-height:460px;overflow-y:auto;">
                @forelse ($loginHistory as $log)
                @php
                    $lt = strtolower($log->logon_type ?? '');
                    $typeMeta = match($lt) {
                        'sso'    => ['SSO',    'an-badge-blue'],
                        'mfa'    => ['MFA',    'an-badge-green'],
                        'logout' => ['Logout', 'an-badge-grey'],
                        default  => ['Login',  'an-badge-purple'],
                    };
                @endphp
                <div class="an-timeline-row">
                    <div class="an-timeline-dot"></div>
                    <div class="an-timeline-body">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="an-badge {{ $typeMeta[1] }}">{{ $typeMeta[0] }}</span>
                            @if ($log->device_info)
                            <span class="an-list-sub">{{ Str::limit($log->device_info, 50) }}</span>
                            @endif
                        </div>
                        <div class="an-timeline-time">
                            {{ $log->logged_in ? \Carbon\Carbon::parse($log->logged_in)->format('d M Y, H:i:s') : '—' }}
                            @if ($log->logged_out)
                                <span class="ms-2 text-muted">→ out {{ \Carbon\Carbon::parse($log->logged_out)->format('H:i') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="an-empty"><i class="fa-solid fa-right-to-bracket"></i><span>No login history</span></div>
                @endforelse
            </div>
        </div>
    </div> --}}

        {{-- Document access --}}
        {{-- <div class="col-12 col-xl-6">
        <div class="an-card">
            <div class="an-card-header">
                <span class="an-card-icon"><i class="fa-solid fa-file-circle-check"></i></span>
                <span class="an-card-title">Documents Accessed</span>
                <span class="an-card-badge ms-auto">By Views</span>
            </div>
            <div class="an-card-body p-0" style="max-height:460px;overflow-y:auto;">
                @forelse ($docAccess as $i => $rf)
                @php
                    $dName = optional($rf->folder)->name ?? '—';
                    $ext   = strtolower(pathinfo($dName, PATHINFO_EXTENSION));
                    $icon  = match(true) {
                        in_array($ext, ['doc','docx']) => ['fa-file-word',      '#2563eb'],
                        in_array($ext, ['xls','xlsx']) => ['fa-file-excel',     '#16a34a'],
                        in_array($ext, ['ppt','pptx']) => ['fa-file-powerpoint','#ea580c'],
                        $ext === 'pdf'                 => ['fa-file-pdf',       '#dc2626'],
                        in_array($ext, ['jpg','jpeg','png','gif','webp']) => ['fa-file-image','#7c3aed'],
                        default                        => ['fa-file',           '#64748b'],
                    };
                @endphp
                <div class="an-list-row">
                    <span class="an-rank">{{ $i + 1 }}</span>
                    <i class="fa-solid {{ $icon[0] }}" style="color:{{ $icon[1] }};font-size:15px;flex-shrink:0;"></i>
                    <div class="an-list-meta overflow-hidden">
                        <div class="an-list-name text-truncate" title="{{ $dName }}">{{ $dName }}</div>
                        <div class="an-list-sub">
                            Last viewed {{ $rf->updated_at ? $rf->updated_at->format('d M Y') : '—' }}
                        </div>
                    </div>
                    <span class="an-badge an-badge-teal ms-auto flex-shrink-0">
                        <i class="fa-solid fa-eye me-1"></i>{{ number_format($rf->view_count) }}
                    </span>
                </div>
                @empty
                <div class="an-empty"><i class="fa-regular fa-file"></i><span>No document access recorded</span></div>
                @endforelse
            </div>
        </div>
    </div> --}}

    </div>

    {{-- ── Activity log ─────────────────────────────────────────────────────────── --}}
    <div class="row g-3 mt-1">
        <div class="col-12">
            <div class="an-card">
                <div class="an-card-header">
                    <span class="an-card-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                    <span class="an-card-title">Activity Log</span>
                </div>
                <div class="an-card-body p-0">
                    @if ($activityLogs->count())
                        <div class="table-responsive">
                            <table class="table rol-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>When</th>
                                        <th class="col-has-filter">
                                            <div class="col-th-inner">
                                                <span>Activity</span>
                                                <button type="button"
                                                    class="col-filter-btn {{ request()->filled('action') ? 'active' : '' }}"
                                                    data-col="action" title="Filter"><i
                                                        class="fa-solid fa-filter"></i></button>
                                            </div>
                                        </th>
                                        <th class="col-has-filter">
                                            <div class="col-th-inner">
                                                <span>Type</span>
                                                <button type="button"
                                                    class="col-filter-btn {{ request()->filled('model') ? 'active' : '' }}"
                                                    data-col="model" title="Filter"><i
                                                        class="fa-solid fa-filter"></i></button>
                                            </div>
                                        </th>
                                        <th>Description</th>
                                        <th>IP</th>
                                        <th class="text-center">Details</th>
                                    </tr>
                                </thead>
                                <tbody id="activityLogsBody">
                                    @foreach ($activityLogs as $log)
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
                                                <span style="font-size:13px;color:#334155;">{!! $descriptions[$log->id] ?? e($log->description) !!}</span>
                                                @if (!empty($paths[$log->id]))
                                                    <div style="font-size:11px;color:#64758d;margin-top:3px;">
                                                        <i class="fa-solid fa-angle-right"
                                                            style="font-size:9px;margin-right:3px;"></i>{{ $paths[$log->id] }}
                                                    </div>
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
                                                    data-user="{{ $displayName }}"
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
                            {{ $activityLogs->links() }}
                        </div>
                    @else
                        <div class="an-empty"><i class="fa-solid fa-clock-rotate-left"></i><span>No activity recorded for
                                this user yet</span></div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Column filter panels (outside table so clicks don't bubble into <th>) ──
         One shared GET form for both panels: submitting from either applies
         whatever's checked in both, as real query-string filters. That's
         what makes them compose correctly with $activityLogs->withQueryString()
         pagination — a client-side row toggle would only ever narrow the 25
         rows already loaded, leaving every other page unfiltered. --}}
    <form method="GET" action="{{ route('analytics.userDetail', $user->id) }}">
        <div class="col-filter-panel" data-col-panel="action">
            <div class="cfp-header">Filter by Activity</div>
            <div class="cfp-body cfp-checkboxes">
                @foreach (['created', 'updated', 'deleted', 'restored', 'login', 'logout', 'password_changed'] as $actionOption)
                    <label class="cfp-check-label">
                        <input type="checkbox" name="action[]" value="{{ $actionOption }}"
                            {{ in_array($actionOption, (array) request('action', [])) ? 'checked' : '' }}>
                        <span>{{ ucfirst(str_replace('_', ' ', $actionOption)) }}</span>
                    </label>
                @endforeach
            </div>
            <div class="cfp-footer">
                <a class="cfp-reset"
                    href="{{ request()->fullUrlWithQuery(['action' => null, 'page' => null]) }}">Reset</a>
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
                        <input type="checkbox" name="model[]" value="{{ $optValue }}"
                            {{ in_array($optValue, (array) request('model', [])) ? 'checked' : '' }}>
                        <span>{{ $modelOption }}</span>
                    </label>
                @endforeach
            </div>
            <div class="cfp-footer">
                <a class="cfp-reset"
                    href="{{ request()->fullUrlWithQuery(['model' => null, 'page' => null]) }}">Reset</a>
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

@push('addOnCss')
    <style>
        @media print {

            .sidebar,
            header,
            .an-header-actions,
            .an-icon-btn {
                display: none !important;
            }

            .an-card {
                box-shadow: none !important;
                border: 1px solid #e2e8f0 !important;
                page-break-inside: avoid;
            }

            .an-card-body[style*="max-height"] {
                max-height: none !important;
                overflow: visible !important;
            }
        }

        /* ── Activity log table (same convention as the Activity Logs page) ── */
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

        /* ── Column filter (Activity, Type) ── */
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

        .an-avatar {
            border-radius: 50%;
            background: #253447;
            color: #fff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .an-avatar-lg {
            width: 46px;
            height: 46px;
            font-size: 18px;
            border-radius: 11px;
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
            height: 48px;
            padding: 0 18px;
            background: #253447;
            box-sizing: border-box;
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

        .an-card-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 99px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
        }

        .an-card-body {
            min-height: 60px;
            flex: 1;
        }

        .an-user-profile-block {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 10px;
            padding-bottom: 18px;
            margin-bottom: 6px;
            border-bottom: 1px solid #f1f5f9;
        }

        .an-user-profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 2px 10px rgba(37, 52, 71, .12);
        }

        .an-user-profile-avatar-icon {
            font-size: 72px;
            color: #94a3b8;
            line-height: 1;
        }

        .an-user-profile-name {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .an-user-info-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .an-user-info-row:last-child {
            border-bottom: none;
        }

        .an-info-label {
            font-size: 11.5px;
            font-weight: 600;
            color: #94a3b8;
            width: 90px;
            flex-shrink: 0;
            padding-top: 2px;
        }

        .an-info-val {
            font-size: 13px;
            color: #1e293b;
            flex: 1;
            min-width: 0;
        }

        .an-bar-chart {
            display: flex;
            align-items: flex-end;
            gap: 5px;
            flex: 1;
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
                    transparent 0, transparent calc(33.33% - 1px),
                    #cdeef3 calc(33.33% - 1px), #cdeef3 33.33%);
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

        .an-timeline-row {
            display: flex;
            gap: 12px;
            padding: 12px 18px;
            border-bottom: 1px solid #f8fafc;
        }

        .an-timeline-row:last-child {
            border-bottom: none;
        }

        .an-timeline-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #253447;
            margin-top: 6px;
            flex-shrink: 0;
        }

        .an-timeline-body {
            flex: 1;
            min-width: 0;
        }

        .an-timeline-time {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 4px;
            font-variant-numeric: tabular-nums;
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

        .an-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 9px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .an-badge-purple {
            background: rgba(37, 52, 71, .10);
            color: #253447;
        }

        .an-badge-teal {
            background: #ccfbf1;
            color: #0f766e;
        }

        .an-badge-blue {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .an-badge-green {
            background: #dcfce7;
            color: #16a34a;
        }

        .an-badge-solid-green {
            background: #0d9488;
            color: #fff;
        }

        .an-badge-grey {
            background: #f1f5f9;
            color: #64748b;
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
    </style>
@endpush

@push('script')
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

            /* ── Column filter panels (Activity, Type) — open/close only;
               applying and resetting are real form submits/links (see the
               shared <form> around the panels), so the filter is part of
               the query string and paginates correctly. ── */
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
        });
    </script>
@endpush
