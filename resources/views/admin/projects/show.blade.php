@extends('admin.layouts.app')

@section('title', ($project->name ?? 'Project') . ' — Overview')
@section('page_title', $project->name ?? 'Project')

@section('content')

    @php
        // Archived == soft-deleted; that's the only status signal now.
        $isArchived = $project->trashed();
        $statusLabel = $isArchived ? 'Archived' : 'Active';
        $statusCls = $isArchived ? 'prj-status-archived' : 'prj-status-active';
    @endphp

    {{-- ── Page header ─────────────────────────────────────────────────────────── --}}
    <div class="prj-show-header">
        <div class="prj-show-icon">
            <i class="fa-solid fa-diagram-project"></i>
        </div>
        <div class="prj-show-header-body">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h5 class="prj-show-title mb-0">{{ $project->name }}</h5>
                <span class="prj-status-badge {{ $statusCls }}">{{ $statusLabel }}</span>
            </div>
            <nav class="prj-breadcrumb mt-1">
                <a href="{{ route('projects.index') }}">Projects Management</a>
                <i class="fa-solid fa-chevron-right"></i>
                <span>{{ Str::limit($project->name, 40) }}</span>
            </nav>
        </div>
        <div class="prj-show-actions">
            <a href="{{ url('folders/shared') }}/#path={{ $project->id }}" class="prj-action-btn prj-action-btn-fileroom">
                <i class="fa-solid fa-folder-open"></i> File Room
            </a>
            @if ($project->trashed())
                <form action="{{ route('projects.restore', $project->id) }}" method="POST" class="d-inline" id="restoreForm">
                    @csrf
                    <button type="button" class="prj-action-btn prj-action-btn-restore" id="restoreBtn">
                        <i class="fa-solid fa-rotate-left"></i> Restore
                    </button>
                </form>
                <a href="{{ route('projects.archived') }}" class="prj-action-btn prj-action-btn-ghost">
                    <i class="fa-solid fa-arrow-left"></i> Back to Archive
                </a>
            @else
                <a href="{{ route('projects.edit', $project) }}" class="prj-action-btn">
                    <i class="fa-solid fa-pen"></i> Edit
                </a>
                <form action="{{ route('projects.archive', $project) }}" method="POST" class="d-inline" id="archiveForm">
                    @csrf
                    <button type="button" class="prj-action-btn prj-action-btn-archive" id="archiveBtn">
                        <i class="fa-solid fa-box-archive"></i> Archive Project
                    </button>
                </form>
                <a href="{{ route('projects.index') }}" class="prj-action-btn prj-action-btn-ghost">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </a>
            @endif
        </div>
    </div>

    {{-- ── Archived banner ──────────────────────────────────────────────────────── --}}
    @if ($project->trashed())
        <div class="prj-readonly-banner">
            <i class="fa-solid fa-lock"></i>
            <div class="prj-readonly-body">
                <strong>This project is archived and read-only.</strong>
                Files remain accessible to permitted users.
            </div>
        </div>
    @endif

    {{-- ── Project meta strip ───────────────────────────────────────────────────── --}}
    <div class="prj-meta-strip">
        <div class="prj-meta-item">
            <span class="prj-meta-icon"><i class="fa-solid fa-user"></i></span>
            <div>
                <div class="prj-meta-label">Created By</div>
                <div class="prj-meta-value">
                    {{ $project->creator ? trim($project->creator->fname . ' ' . $project->creator->lname) : '—' }}
                </div>
            </div>
        </div>

        <div class="prj-meta-item">
            <span class="prj-meta-icon"><i class="fa-solid fa-calendar"></i></span>
            <div>
                <div class="prj-meta-label">Created</div>
                <div class="prj-meta-value">{{ $project->created_at->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    {{-- ── Widgets ───────────────────────────────────────────────────────────────── --}}
    <div class="row g-3">

        {{-- Widget 1: Document Updates --}}
        <div class="col-12 col-lg-4">
            <div class="prj-widget">
                <div class="prj-widget-header">
                    <span class="prj-widget-icon prj-wicon-green">
                        <i class="fa-solid fa-file-pen"></i>
                    </span>
                    <span class="prj-widget-title">Document Updates</span>
                    <span class="prj-widget-badge ms-auto">Past 7 days</span>
                </div>
                <div class="prj-widget-body p-0">
                    @forelse ($documentUpdates as $doc)
                        <div class="prj-list-row">
                            <span class="prj-file-icon">
                                @php
                                    $ext = strtolower(pathinfo($doc->name, PATHINFO_EXTENSION));
                                    $fi = match (true) {
                                        in_array($ext, ['doc', 'docx']) => ['fa-file-word', '#2563eb'],
                                        in_array($ext, ['xls', 'xlsx']) => ['fa-file-excel', '#16a34a'],
                                        in_array($ext, ['ppt', 'pptx']) => ['fa-file-powerpoint', '#ea580c'],
                                        $ext === 'pdf' => ['fa-file-pdf', '#dc2626'],
                                        in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) => [
                                            'fa-file-image',
                                            '#7c3aed',
                                        ],
                                        default => ['fa-file', '#64748b'],
                                    };
                                @endphp
                                <i class="fa-solid {{ $fi[0] }}" style="color:{{ $fi[1] }};"></i>
                            </span>
                            <span class="prj-list-name text-truncate"
                                title="{{ $doc->name }}">{{ $doc->name }}</span>
                            <span class="prj-list-meta ms-auto flex-shrink-0">
                                {{ $doc->updated_at->format('d M, H:i') }}
                            </span>
                        </div>
                    @empty
                        <div class="prj-empty-state">
                            <i class="fa-regular fa-file-lines"></i>
                            <span>No updates in the past 7 days</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Widget 2: Top Documents by Views --}}
        <div class="col-12 col-lg-4">
            <div class="prj-widget">
                <div class="prj-widget-header">
                    <span class="prj-widget-icon prj-wicon-blue">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                    <span class="prj-widget-title">Top Documents by Views</span>
                </div>
                <div class="prj-widget-body p-0">
                    @forelse ($topDocuments as $i => $doc)
                        <div class="prj-list-row">
                            <span class="prj-rank-badge">{{ $i + 1 }}</span>
                            <span class="prj-list-name text-truncate"
                                title="{{ $doc->name }}">{{ $doc->name }}</span>
                            <span class="prj-view-count ms-auto flex-shrink-0">
                                <i class="fa-solid fa-eye me-1 opacity-50"></i>{{ number_format($doc->view_count) }}
                            </span>
                        </div>
                    @empty
                        <div class="prj-empty-state">
                            <i class="fa-regular fa-eye"></i>
                            <span>No view data available</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Widget 3: Top Users by Activity --}}
        <div class="col-12 col-lg-4">
            <div class="prj-widget">
                <div class="prj-widget-header">
                    <span class="prj-widget-icon prj-wicon-amber">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <span class="prj-widget-title">Top Users by Activity</span>
                </div>
                <div class="prj-widget-body p-0">
                    @forelse ($topUsers as $i => $user)
                        <div class="prj-list-row">
                            <span class="prj-rank-badge">{{ $i + 1 }}</span>
                            @if ($user->avatar ?? null)
                                <img class="prj-user-avatar" src="{{ asset('storage/' . $user->avatar) }}" style="object-fit:cover;" alt="">
                            @else
                                <i class="fa-solid fa-circle-user" style="font-size:28px;color:#94a3b8;flex-shrink:0;line-height:1;"></i>
                            @endif
                            <div class="prj-list-user overflow-hidden">
                                <div class="prj-list-name text-truncate">{{ $user->name ?? 'Unknown' }}</div>
                                @if (!empty($user->email))
                                    <div class="prj-list-sub text-truncate">{{ $user->email }}</div>
                                @endif
                            </div>
                            <span class="prj-view-count ms-auto flex-shrink-0">
                                <i
                                    class="fa-solid fa-arrow-right-to-bracket me-1 opacity-50"></i>{{ number_format($user->login_count ?? 0) }}
                            </span>
                        </div>
                    @empty
                        <div class="prj-empty-state">
                            <i class="fa-regular fa-user"></i>
                            <span>No activity data available</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

@endsection

@push('addOnCss')
    <style>
        /* ── Page header ──────────────────────────────────────────────────────────── */
        .prj-show-header {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 16px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .07);
        }

        .prj-show-icon {
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

        .prj-show-header-body {
            flex: 1;
            min-width: 0;
        }

        .prj-show-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
        }

        .prj-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #94a3b8;
        }

        .prj-breadcrumb a {
            color: #64748b;
            text-decoration: none;
        }

        .prj-breadcrumb a:hover {
            color: #253447;
        }

        .prj-breadcrumb i {
            font-size: 9px;
        }

        .prj-show-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .prj-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: background .13s, color .13s, border-color .13s;
            background: #253447;
            color: #fff;
            border: 1.5px solid #253447;
        }

        .prj-action-btn:hover {
            background: #1a2737;
            border-color: #1a2737;
            color: #fff;
        }

        .prj-action-btn-ghost {
            background: #f8fafc;
            color: #475569;
            border-color: #e2e8f0;
        }

        .prj-action-btn-ghost:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #253447;
        }

        .prj-action-btn-fileroom {
            background: #0f766e;
            border-color: #0f766e;
            color: #fff;
        }

        .prj-action-btn-fileroom:hover {
            background: #0d6460;
            border-color: #0d6460;
            color: #fff;
        }

        .prj-action-btn-restore {
            background: #f0fdf4;
            border-color: #16a34a;
            color: #16a34a;
            cursor: pointer;
        }

        .prj-action-btn-restore:hover {
            background: #16a34a;
            border-color: #16a34a;
            color: #fff;
        }

        .prj-action-btn-archive {
            background: #f8fafc;
            border-color: #94a3b8;
            color: #475569;
            cursor: pointer;
        }

        .prj-action-btn-archive:hover {
            background: #64748b;
            border-color: #64748b;
            color: #fff;
        }

        /* ── Archived banner ──────────────────────────────────────────────────────── */
        .prj-readonly-banner {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fefce8;
            border: 1px solid #fde68a;
            border-radius: 11px;
            padding: 13px 18px;
            margin-bottom: 16px;
            font-size: 13.5px;
            color: #92400e;
        }

        .prj-readonly-banner>i {
            font-size: 16px;
            margin-top: 1px;
            flex-shrink: 0;
        }

        .prj-readonly-body {
            flex: 1;
            line-height: 1.5;
        }

        .prj-ret-pill {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            border-radius: 99px;
            font-size: 11.5px;
            font-weight: 600;
            margin-left: 6px;
        }

        .prj-ret-ok {
            background: #dcfce7;
            color: #16a34a;
        }

        .prj-ret-soon {
            background: #fef9c3;
            color: #b45309;
        }

        .prj-ret-expired {
            background: #fee2e2;
            color: #dc2626;
        }

        /* ── Status badge ─────────────────────────────────────────────────────────── */
        .prj-status-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11.5px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 99px;
            white-space: nowrap;
        }

        .prj-status-active {
            background: #16a34a;
            color: #fff;
        }

        .prj-status-closed {
            background: #dc2626;
            color: #fff;
        }

        .prj-status-archived {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* ── Meta strip ───────────────────────────────────────────────────────────── */
        .prj-meta-strip {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            margin-bottom: 16px;
            overflow: hidden;
        }

        .prj-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 20px;
            flex: 1;
            min-width: 140px;
            border-right: 1px solid #f1f5f9;
        }

        .prj-meta-item:last-child {
            border-right: none;
        }

        .prj-meta-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .prj-meta-label {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .prj-meta-value {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            margin-top: 1px;
        }

        /* ── Widgets ──────────────────────────────────────────────────────────────── */
        .prj-widget {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            border: 1px solid #f1f5f9;
            overflow: hidden;
            height: 100%;
        }

        .prj-widget-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 13px 18px;
            background: #253447;
            border-bottom: none;
        }

        .prj-widget-icon {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
            background: rgba(255, 255, 255, .12);
            color: #fff;
        }

        .prj-wicon-navy,
        .prj-wicon-green,
        .prj-wicon-blue,
        .prj-wicon-amber {
            background: rgba(255, 255, 255, .12);
            color: #fff;
        }

        .prj-widget-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
        }

        .prj-widget-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 2px 9px;
            border-radius: 99px;
            background: rgba(255, 255, 255, .15);
            color: #fff;
        }

        .prj-widget-body {
            min-height: 80px;
        }

        /* ── List rows ────────────────────────────────────────────────────────────── */
        .prj-list-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 18px;
            border-bottom: 1px solid #f8fafc;
            transition: background .1s;
        }

        .prj-list-row:last-child {
            border-bottom: none;
        }

        .prj-list-row:hover {
            background: #f8fafc;
        }

        .prj-file-icon {
            width: 22px;
            text-align: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .prj-list-name {
            font-size: 13px;
            font-weight: 500;
            color: #1e293b;
        }

        .prj-list-sub {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 1px;
        }

        .prj-list-meta {
            font-size: 12px;
            color: #94a3b8;
            font-variant-numeric: tabular-nums;
        }

        .prj-list-user {
            flex: 1;
            min-width: 0;
        }

        .prj-rank-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 11px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .prj-view-count {
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
            font-variant-numeric: tabular-nums;
        }

        .prj-user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #253447;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ── Empty state ──────────────────────────────────────────────────────────── */
        .prj-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 36px 20px;
            color: #94a3b8;
            font-size: 13px;
        }

        .prj-empty-state i {
            font-size: 24px;
            opacity: .4;
        }

        /* ── Responsive ───────────────────────────────────────────────────────────── */
        @media (max-width: 768px) {
            .prj-show-header {
                flex-wrap: wrap;
            }

            .prj-show-actions {
                width: 100%;
                justify-content: flex-end;
            }

            .prj-meta-item {
                min-width: 45%;
            }

            .prj-action-btn {
                font-size: 12px;
                padding: 7px 12px;
            }
        }
    </style>
@endpush

@push('script')
    <script>
        $(function() {
            $('#archiveBtn').on('click', function() {
                Swal.fire({
                    title: 'Close this project?',
                    html: '<div class="swal-theme-icon" style="background:rgba(37,52,71,.08);color:#253447;"><i class="fa-solid fa-box-archive"></i></div>It will be moved to the archive and become read-only. You can restore it at any time.',
                    width: '380px', showCancelButton: true,
                    confirmButtonColor: '#253447', confirmButtonText: 'Yes, close it',
                    cancelButtonText: 'Cancel', customClass: { popup: 'swal-theme' }, reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) $('#archiveForm').submit();
                });
            });

            $('#restoreBtn').on('click', function() {
                Swal.fire({
                    title: 'Restore this project?',
                    html: '<div class="swal-theme-icon" style="background:rgba(37,52,71,.08);color:#253447;"><i class="fa-solid fa-rotate-left"></i></div>It will be moved back to Active status.',
                    width: '380px', showCancelButton: true,
                    confirmButtonColor: '#253447', confirmButtonText: 'Yes, restore',
                    cancelButtonText: 'Cancel', customClass: { popup: 'swal-theme' }, reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) $('#restoreForm').submit();
                });
            });
        });
    </script>
@endpush
