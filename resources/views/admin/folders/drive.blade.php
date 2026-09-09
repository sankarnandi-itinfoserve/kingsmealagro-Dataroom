@extends('admin.layouts.app')
@section('title', 'Project Folders')
@section('page_title', 'Project Folders')

@section('content')

    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card">

            <div class="fb-header-row">
                <div>
                    <div class="fb-nav-line">
                        <button type="button" class="fb-back-btn" id="fbBackBtn">
                            <i class="fa-solid fa-arrow-left"></i> Back
                        </button>

                        <nav class="fb-breadcrumb" aria-label="Breadcrumb">
                            <a href="#" class="fb-crumb" data-level="0">Project Folders</a>
                        </nav>
                    </div>
                </div>

                <div class="fb-header-actions">
                    <div class="fb-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="fbSearchInput" placeholder="Search">
                    </div>

                    <div class="fb-view-toggle" role="group" aria-label="View mode">
                        <button type="button" class="fb-view-btn active" data-view="list">
                            <i class="fa-solid fa-list"></i>
                        </button>
                        <button type="button" class="fb-view-btn" data-view="grid">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="fb-layout">

                <section class="fb-main">

                    <div class="fb-bulk-row fb-subscription-row">
                        {{-- Select + count --}}
                        <label class="fb-subscribe-label">
                            <input type="checkbox" id="fbSelectAll">
                            Select all
                        </label>
                        <span class="fb-sel-badge" id="fbSelectedCount">0 selected</span>

                        <span class="fb-toolbar-sep"></span>

                        {{-- Actions --}}
                        <button type="button" class="fb-tool-btn" id="fbBulkDownloadBtn">
                            <i class="fa-solid fa-cloud-arrow-down" style="color:#0284c7;"></i> Bulk Download
                        </button>
                        <button type="button" class="fb-tool-btn" data-action="favorite-current">
                            <i class="fa-solid fa-star" style="color:#f59e0b;"></i> Favorite
                        </button>
                    </div>

                    <div id="fbListView" class="fb-view-panel">
                        <div class="table-responsive">
                            <table class="table fb-table align-middle">
                                <thead>
                                    <tr>
                                        <th class="fb-col-check"></th>
                                        <th class="sortable" data-sort="name">Name <i class="fa-solid fa-sort"></i></th>
                                        <th class="sortable" data-sort="size">Size <i class="fa-solid fa-sort"></i></th>
                                        <th class="sortable" data-sort="modified">Last Modified <i
                                                class="fa-solid fa-sort"></i></th>
                                        <th class="sortable" data-sort="creator">Creator <i class="fa-solid fa-sort"></i>
                                        </th>
                                        <th class="fb-col-actions">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="fbListBody"></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="fbGridView" class="fb-view-panel d-none">
                        <div class="row g-3" id="fbGridBody"></div>
                    </div>

                </section>
            </div>
        </div>
    </div>

    <div id="fbDownloadModal" class="fb-modal fb-download-modal d-none">
        <div class="fb-modal-backdrop"></div>
        <div class="fb-download-dialog">
            <div class="fb-download-icon"><i class="fa-solid fa-cloud-arrow-down"></i></div>
            <p class="fb-download-title" id="fbDownloadTitle">Preparing download…</p>
            <div class="fb-download-progress">
                <div class="fb-download-progress-bar" id="fbDownloadProgressBar"></div>
            </div>
            <p class="fb-download-percent" id="fbDownloadPercent">0%</p>
            <button type="button" class="fb-download-cancel" id="fbDownloadCancelBtn">Cancel</button>
        </div>
    </div>

@endsection

@push('addOnCss')
    <style>
        .fb-nav-line {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .fb-back-btn {
            border: 1px solid #dbe4f0;
            background: #ffffff;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            padding: 5px 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .fb-back-btn:hover {
            border-color: #93c5fd;
            color: #1d4ed8;
        }

        .fb-back-btn:disabled {
            opacity: .45;
            cursor: not-allowed;
            border-color: #dbe4f0;
            color: #64748b;
        }

        .fb-page-title {
            margin: 0;
            font-size: 30px;
            font-weight: 700;
            color: #111827;
        }



        .fb-sidebar-panel {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #ffffff;
            padding: 10px;
            min-height: 300px;
            overflow: auto;
        }

        .fb-sidebar-head {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 1px solid #e5e7eb;
            padding: 2px 2px 8px;
            margin-bottom: 8px;
        }

        /* Upload area (copied from upload view) */
        .upload-card {
            border: 1px solid #e6eef8;
            border-radius: 12px;
            padding: 18px;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(13, 38, 76, 0.04);
        }

        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 28px;
            text-align: center;
            transition: background .12s ease, border-color .12s ease, box-shadow .12s ease;
            cursor: pointer;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 14px;
            min-height: 120px;
        }

        .upload-dropzone.dragover {
            background: #f1f5ff;
            border-color: #60a5fa;
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.08) inset
        }

        .upload-dropzone .dz-icon {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #eef2ff, #fff);
            border-radius: 8px;
            color: #2563eb;
            font-size: 20px;
            box-shadow: 0 6px 18px rgba(14, 165, 233, 0.06)
        }

        .upload-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 12px;
            flex-wrap: wrap
        }

        .btn-upload,
        .btn-clear {
            background: #2563eb;
            color: #fff;
            border: 0;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer
        }

        .btn-clear {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb
        }

        .upload-list {
            margin-top: 16px;
            display: grid;
            gap: 10px
        }

        .upload-item {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 8px;
            border-radius: 8px;
            background: #fbfdff;
            border: 1px solid #eef2ff
        }

        .upload-thumb {
            width: 44px;
            height: 44px;
            border-radius: 6px;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 1px solid #e6eef8;
            flex-shrink: 0
        }

        .upload-meta {
            flex: 1;
            min-width: 0
        }

        .upload-meta .name {
            font-weight: 600;
            color: #0f172a;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .upload-meta .size {
            font-size: 12px;
            color: #667085
        }

        .upload-progress {
            height: 6px;
            background: #f1f5f9;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 6px
        }

        .upload-progress>i {
            display: block;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #06b6d4, #3b82f6)
        }

        .upload-item .actions {
            display: flex;
            gap: 8px;
            align-items: center
        }

        .upload-remove {
            background: transparent;
            border: 0;
            color: #ef4444;
            cursor: pointer;
            font-weight: 700
        }

        .upload-done {
            background: transparent;
            border: 0;
            color: #16a34a;
            cursor: default;
            font-weight: 700
        }

        .fb-tree-toggle {
            border: 0;
            background: transparent;
            color: #64748b;
            width: 14px;
            min-width: 14px;
            padding: 0;
            line-height: 1;
            cursor: pointer;
        }

        .fb-tree-spacer {
            width: 0px;
            min-width: 0px;
        }

        .fb-tree-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
        }

        .fb-tree-label span,
        .fb-tree-label a {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
            /* Neutralize the global .submenu a rule (sidebar nav menu items) —
               this tree panel happens to sit inside a .submenu-classed
               container, so a plain <a> here would otherwise inherit that
               rule's button-like padding/flex sizing meant for nav links,
               not file names. */
            display: inline-block;
            padding: 0;
            vertical-align: middle;
        }

        .fb-file-pdf-icon {
            color: #dc2626;
        }

        .fb-file-word-icon {
            color: #2563eb;
        }

        .fb-file-excel-icon {
            color: #16a34a;
        }

        .fb-file-ppt-icon {
            color: #ea580c;
        }

        .fb-file-zip-icon {
            color: #b45309;
        }

        .fb-file-img-icon {
            color: #7c3aed;
        }

        .fb-file-icon {
            color: #64748b;
        }

        .fb-bulk-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-size: 12px;
            color: #475569;
            flex-wrap: wrap;
        }

        .fb-sel-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
        }

        .fb-toolbar-sep {
            width: 1px;
            height: 20px;
            background: #e2e8f0;
            flex-shrink: 0;
        }

        .fb-toolbar-sep.ms-auto {
            margin-left: auto !important;
        }

        .fb-tool-btn-primary {
            background: #0d9488 !important;
            color: #fff !important;
            border-color: #05a194 !important;
        }

        .fb-tool-btn-primary:hover {
            background: #05a194 !important;
            border-color: #0d9488 !important;
            color: #fff !important;
        }

        .fb-tool-btn-outline {
            background: #fff !important;
            color: #253447 !important;
            border-color: #253447 !important;
        }

        .fb-tool-btn-outline:hover {
            background: #f1f5f9 !important;
            border-color: #1a2737 !important;
            color: #1a2737 !important;
        }

        .fb-col-check {
            width: 34px;
        }

        .fb-col-actions {
            width: 100px;
        }

        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .sortable i {
            margin-left: 6px;
            color: #94a3b8;
        }

        .fb-hidden {
            display: none !important;
        }

        @media (max-width: 768px) {
            .fb-layout {
                grid-template-columns: 1fr;
            }

            .fb-page-title {
                font-size: 24px;
            }

            .fb-col-actions {
                width: 100px;
            }
        }
    </style>
    <style>
        /* modal styles for upload iframe */
        .fb-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .fb-modal.d-none {
            display: none;
        }

        /* SweetAlert2's default z-index (1060) sits below our own .fb-modal
                   (2000) — without this, a Swal dialog opened while a .fb-modal is
                   still up (e.g. the copy-conflict prompt over the "Copy to..."
                   picker) renders behind it instead of on top. */
        .swal2-container {
            z-index: 2500 !important;
        }

        .fb-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(2, 6, 23, 0.5);
            backdrop-filter: blur(2px);
        }

        .fb-modal-dialog {
            position: relative;
            width: 900px;
            max-width: calc(100% - 48px);
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.4);
            overflow: hidden;
            z-index: 2100;
        }

        .fb-modal-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid #eef2ff;
        }

        .fb-modal-title {
            margin: 0;
            font-weight: 700
        }

        .fb-modal-close {
            margin-left: auto;
            background: transparent;
            border: 0;
            font-size: 20px;
            cursor: pointer
        }

        .fb-modal-body {
            padding: 14px
        }

        .fb-download-dialog {
            position: relative;
            width: 360px;
            max-width: calc(100% - 32px);
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.4);
            z-index: 2100;
            padding: 26px 24px 22px;
            text-align: center;
        }

        .fb-download-icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 12px;
            border-radius: 50%;
            background: #eff6ff;
            color: #2563eb;
            font-size: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .fb-download-title {
            margin: 0 0 14px;
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
        }

        .fb-download-progress {
            height: 8px;
            background: #f1f5f9;
            border-radius: 6px;
            overflow: hidden;
        }

        .fb-download-progress-bar {
            height: 100%;
            width: 0%;
            border-radius: 6px;
            background: linear-gradient(90deg, #06b6d4, #2563eb);
            transition: width .15s ease;
        }

        .fb-download-percent {
            margin: 8px 0 16px;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
        }

        .fb-download-cancel {
            height: 34px;
            padding: 0 18px;
            border: 1.5px solid #dbe4f0;
            border-radius: 8px;
            background: #fff;
            color: #64748b;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
        }

        .fb-download-cancel:hover {
            border-color: #cbd5e1;
            color: #334155;
            background: #f8fafc;
        }
    </style>
    <style>
        /* Favorited button state */
        .fb-row-btn.fb-fav-active {
            background: #fffbeb;
            border-color: #fcd34d;
            color: #d97706;
        }

        .fb-row-btn.fb-fav-active:hover {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #b45309;
        }


        /* Highlight checked rows — target td to override Bootstrap 5's cell-level bg */
        #fbListBody tr.fb-row-selected>td {
            background-color: #dbeafe !important;
        }

        #fbListBody tr.fb-row-selected:hover>td {
            background-color: #bfdbfe !important;
        }

        .fb-grid-card.fb-row-selected {
            background: #f1f5f9 !important;
            border-color: #cbd5e1;
        }

        /* ── Create Folder Modal ── */
        .cf-dialog {
            position: relative;
            width: 420px;
            max-width: calc(100% - 32px);
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, .18);
            overflow: hidden;
            z-index: 2100;
        }

        .cf-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 22px 18px;
            background: linear-gradient(135deg, #1a2737 0%, #253447 100%);
        }

        .cf-header-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, .12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            flex-shrink: 0;
        }

        .cf-header-text {
            flex: 1;
            min-width: 0;
        }

        .cf-title {
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            margin: 0 0 2px;
            letter-spacing: -.2px;
        }

        .cf-sub {
            font-size: 12px;
            color: rgba(255, 255, 255, .55);
            margin: 0;
        }

        .cf-close {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: rgba(255, 255, 255, .1);
            border: none;
            color: rgba(255, 255, 255, .7);
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s, color .15s;
            flex-shrink: 0;
        }

        .cf-close:hover {
            background: rgba(255, 255, 255, .2);
            color: #fff;
        }

        .cf-body {
            padding: 22px 22px 6px;
        }

        .cf-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .cf-input-wrap {
            position: relative;
        }

        .cf-input-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: #94a3b8;
            pointer-events: none;
        }

        .cf-input {
            width: 100%;
            height: 42px;
            padding: 0 14px 0 34px;
            border: 1.5px solid #dbe4f0;
            border-radius: 10px;
            font-size: 13px;
            color: #1e293b;
            background: #f9fafb;
            outline: none;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .cf-input:focus {
            border-color: #cbd5e1;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(100, 116, 139, .10);
        }

        .cf-hint {
            font-size: 11.5px;
            color: #94a3b8;
            margin: 6px 0 0;
        }

        .cf-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 22px 20px;
            border-top: 1px solid #f1f5f9;
            margin-top: 16px;
        }

        .cf-btn-cancel {
            height: 38px;
            padding: 0 18px;
            border: 1.5px solid #dbe4f0;
            border-radius: 9px;
            background: #fff;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: border-color .15s, color .15s, background .15s;
        }

        .cf-btn-cancel:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #334155;
        }

        .cf-btn-create {
            height: 38px;
            padding: 0 20px;
            border: none;
            border-radius: 9px;
            background: linear-gradient(135deg, #253447, #1a2737);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 7px;
            box-shadow: 0 3px 10px rgba(37, 52, 71, .28);
            transition: opacity .15s, transform .1s, box-shadow .15s;
        }

        .cf-btn-create:hover {
            opacity: .92;
            transform: translateY(-1px);
            box-shadow: 0 5px 16px rgba(37, 52, 71, .38);
        }

        .cf-btn-create:disabled {
            opacity: .6;
            cursor: not-allowed;
            transform: none;
        }

        /* ── "Copy to..." destination picker ── */
        .cp-dialog {
            width: 460px;
        }

        .cp-body {
            padding: 14px 14px 6px;
        }

        .cp-tree {
            max-height: 360px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 6px;
        }

        .cp-node {
            margin: 0;
        }

        .cp-item {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 7px 8px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 13px;
            color: #334155;
        }

        .cp-item:hover {
            background: #f8fafc;
        }

        .cp-item.cp-selected {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 700;
        }

        .cp-item.cp-disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .cp-toggle {
            width: 16px;
            height: 16px;
            border: none;
            background: none;
            padding: 0;
            color: #94a3b8;
            font-size: 10px;
            cursor: pointer;
            flex-shrink: 0;
            transition: transform .13s;
        }

        .cp-node.open>.cp-item .cp-toggle {
            transform: rotate(90deg);
        }

        .cp-spacer {
            width: 16px;
            flex-shrink: 0;
        }

        .cp-folder-icon {
            color: #f59e0b;
            font-size: 13px;
            flex-shrink: 0;
        }

        .cp-name {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1 1 auto;
            min-width: 0;
        }

        .cp-children {
            display: none;
            margin-left: 20px;
            border-left: 1px dashed #e2e8f0;
            padding-left: 6px;
        }

        .cp-node.open>.cp-children {
            display: block;
        }

        .cp-empty {
            font-size: 12.5px;
            color: #94a3b8;
            padding: 8px 4px;
            margin: 0;
        }

        .cp-search-wrap {
            position: relative;
            margin-bottom: 10px;
        }

        .cp-search-icon {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 12px;
            color: #94a3b8;
            pointer-events: none;
        }

        .cp-search-input {
            width: 100%;
            height: 36px;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            padding: 0 12px 0 32px;
            font-size: 13px;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .cp-search-input:focus {
            border-color: #93c5fd;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .08);
        }

        .cp-file-icon {
            color: #94a3b8;
            font-size: 13px;
            flex-shrink: 0;
        }

        .cp-file-row {
            cursor: default;
            color: #94a3b8;
        }

        .cp-item.cp-match .cp-name {
            color: #1d4ed8;
            font-weight: 700;
        }
    </style>
@endpush

@push('script')
    <script>
        $(function() {
            const rootFolder = @json($rootFolderData);
            const csrfToken = '{{ csrf_token() }}';

            const toggleFavoriteUrl = '{{ url('/toggle-favorite') }}';
            const downloadMultipleUrl = '{{ route('folders.mdownloadMultiple') }}';

            let activeView = localStorage.getItem('fb_view_mode') === 'grid' ? 'grid' : 'list';
            let activeSort = {
                key: 'name',
                direction: 'asc'
            };
            let query = '';
            const pathStack = [rootFolder];
            const selected = new Set();
            const expandedTreeIds = new Set();
            // Sidebar "Project Folders" search box (distinct from the main
            // list's own `query` search above) — filters the tree itself.
            let treeSearchQuery = '';

            function nodeMatchesTreeSearch(item) {
                if (!treeSearchQuery) return true;
                if ((item.name || '').toLowerCase().includes(treeSearchQuery)) return true;
                return (item.children || []).some(nodeMatchesTreeSearch);
            }

            // Sidebar tree collapse/expand toggle (the "−"/"+" icon next to
            // Project Folders) — purely a tree-display concern, never
            // touches pathStack/query/selected, so the page itself never
            // changes when this is used.
            let treeCollapsed = false;
            let savedExpandedTreeIds = null;

            function setTreeCollapseIcon(collapsed) {
                $('#fbGoToRootBtn i')
                    .toggleClass('fa-minus', !collapsed)
                    .toggleClass('fa-plus', collapsed);
            }

            function currentFolder() {
                return pathStack[pathStack.length - 1];
            }

            function currentItems() {
                return currentFolder().children || [];
            }

            function rootTreeKey() {
                return `root:${rootFolder.id}`;
            }

            function nodeTreeKey(node) {
                return `${node.type || 'folder'}:${node.id}`;
            }

            function findFolderPath(node, targetKey, trail = [], isRootNode = false) {
                const nextTrail = [...trail, node];
                const currentKey = isRootNode ? rootTreeKey() : nodeTreeKey(node);

                if (currentKey === targetKey) {
                    return nextTrail;
                }

                const children = node.children || [];
                for (const child of children) {
                    if (child.type !== 'folder') continue;
                    const found = findFolderPath(child, targetKey, nextTrail, false);
                    if (found) return found;
                }

                return null;
            }

            function setExpandedToCurrentPath() {
                expandedTreeIds.clear();

                pathStack.forEach((node, index) => {
                    if (index === 0) {
                        expandedTreeIds.add(rootTreeKey());
                        return;
                    }
                    expandedTreeIds.add(nodeTreeKey(node));
                });

                // Real navigation supersedes any explicit collapse — the
                // "+" icon would otherwise keep showing even though the
                // tree just expanded again to reveal the current path.
                treeCollapsed = false;
                savedExpandedTreeIds = null;
                setTreeCollapseIcon(false);
            }

            function openTreeMenu() {
                const $menu = $('#treeViewMenu');
                if ($menu.length) {
                    $menu.addClass('open');
                    $menu.find('> .menu-link').addClass('active');
                }
            }

            function updateBreadcrumb() {
                const staticPrefix = `<a href="#" class="fb-crumb" data-level="0">Project Folders</a>`;

                // Skip the root folder (index 0) when building dynamic crumbs
                const dynamicParts = pathStack.slice(1).map((folder, idx) => {
                    const index = idx + 1; // actual index in pathStack
                    const absoluteLevel = index + 1; // level offset: staticPrefix occupies level 0
                    const isLast = index === pathStack.length - 1;
                    if (isLast) {
                        return `<span class="fb-crumb-current" id="fbCurrentFolder">${folder.name}</span>`;
                    }
                    return `<a href="#" class="fb-crumb fb-path-crumb" data-level="${absoluteLevel}">${folder.name}</a><span class="fb-crumb-sep">&gt;</span>`;
                }).join('');

                if (dynamicParts) {
                    $('.fb-breadcrumb').html(staticPrefix + '<span class="fb-crumb-sep">&gt;</span>' +
                        dynamicParts);
                } else {
                    $('.fb-breadcrumb').html(staticPrefix);
                }
                $('.fb-page-title').text(currentFolder().name);
                $('#fbBackBtn').prop('disabled', pathStack.length <= 1);
            }

            function filteredRows() {
                return currentItems()
                    .filter(row => row.name.toLowerCase().includes(query.toLowerCase()));
            }

            function sortedRows() {
                const rows = filteredRows().slice();
                if (!activeSort.key) {
                    return rows;
                }
                rows.sort((a, b) => {
                    let av;
                    let bv;

                    if (activeSort.key === 'size') {
                        av = a.sizeValue;
                        bv = b.sizeValue;
                    } else if (activeSort.key === 'modified') {
                        av = a.modifiedTs;
                        bv = b.modifiedTs;
                    } else {
                        av = (a[activeSort.key] || '').toString().toLowerCase();
                        bv = (b[activeSort.key] || '').toString().toLowerCase();
                    }

                    if (av < bv) return activeSort.direction === 'asc' ? -1 : 1;
                    if (av > bv) return activeSort.direction === 'asc' ? 1 : -1;
                    return 0;
                });
                return rows;
            }

            function getItemIcon(row) {
                if (row.type === 'folder') return {
                    fa: 'fa-folder',
                    cls: 'fb-folder-icon'
                };
                const ext = (row.ext || row.name.split('.').pop()).toLowerCase();
                if (ext === 'pdf') return {
                    fa: 'fa-file-pdf',
                    cls: 'fb-file-pdf-icon'
                };
                if (['doc', 'docx'].includes(ext)) return {
                    fa: 'fa-file-word',
                    cls: 'fb-file-word-icon'
                };
                if (['xls', 'xlsx', 'csv'].includes(ext)) return {
                    fa: 'fa-file-excel',
                    cls: 'fb-file-excel-icon'
                };
                if (['ppt', 'pptx'].includes(ext)) return {
                    fa: 'fa-file-powerpoint',
                    cls: 'fb-file-ppt-icon'
                };
                if (['zip', 'rar', '7z'].includes(ext)) return {
                    fa: 'fa-file-zipper',
                    cls: 'fb-file-zip-icon'
                };
                if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) return {
                    fa: 'fa-file-image',
                    cls: 'fb-file-img-icon'
                };
                return {
                    fa: 'fa-file',
                    cls: 'fb-file-icon'
                };
            }

            function buildTreeRows(node, depth) {
                const children = (node.children || [])
                    .filter(nodeMatchesTreeSearch)
                    .slice().sort((a, b) => {
                        const an = (a.name || '').toString();
                        const bn = (b.name || '').toString();
                        return an.localeCompare(bn, undefined, {
                            sensitivity: 'base',
                            numeric: true
                        });
                    });

                return children.map((item) => {
                    const isFolder = item.type === 'folder';
                    const hasChildren = isFolder && (item.children || []).length > 0;
                    const itemKey = nodeTreeKey(item);
                    // While searching, every folder that survived the filter
                    // above necessarily contains a match somewhere inside it —
                    // force it open so that match is actually visible.
                    const isExpanded = hasChildren && (!!treeSearchQuery || expandedTreeIds.has(itemKey));
                    const isActive = String(currentFolder().id) === String(item.id);
                    const {
                        fa,
                        cls
                    } = getItemIcon(item);
                    const treeNameHtml = !isFolder ?
                        `<a href="javascript:void(0)" class="fb-name-link fb-tree-download-trigger" data-id="${item.id}" data-type="${item.type}" title="${item.name}">${item.name}</a>` :
                        `<span title="${item.name}">${item.name}</span>`;

                    const row = `
                        <div class="fb-tree-node ${isActive ? 'active' : ''}" data-tree-id="${item.id}" data-tree-key="${itemKey}" data-tree-type="${item.type}" style="padding-left:${depth * 14 + 20}px; --depth-left: ${depth * 14}px;">
                            <span class="fb-tree-spacer"></span>
                            ${hasChildren
                                ? `<button type="button" class="fb-tree-toggle" data-tree-toggle="${itemKey}" style="left:${depth * 14}px"><i class="fa-solid ${isExpanded ? 'fa-minus' : 'fa-plus'}"></i></button>`
                                : ''}
                            <span class="fb-tree-label">
                                <i class="fa-solid ${fa} ${cls}"></i>
                                ${treeNameHtml}
                            </span>
                        </div>
                    `;

                    if (!isFolder || !hasChildren || !isExpanded) {
                        return row;
                    }

                    return row + buildTreeRows(item, depth + 1);
                }).join('');
            }

            function renderTree() {
                // Render only the root's children (do not display the root "Documents" node)
                const html = buildTreeRows(rootFolder, 0);
                $('#fbTreeView').html(html);
            }

            window._fbTreeSearch = function(q) {
                treeSearchQuery = (q || '').trim().toLowerCase();
                renderTree();
            };

            function renderList() {
                const rows = sortedRows();

                const html = rows.map((row) => {
                    const checked = selected.has(row.id) ? 'checked' : '';
                    const starClass = row.favorite ? 'fa-solid' : 'fa-regular';
                    const favBtnCls = row.favorite ? 'fb-row-btn fb-fav-active' : 'fb-row-btn';
                    const {
                        fa: icon,
                        cls: iconCls
                    } = getItemIcon(row);
                    const clickable = row.type === 'folder' ? 'fb-item-folder' : '';

                    const selectedCls = selected.has(row.id) ? 'fb-row-selected' : '';
                    const nameHtml = row.type === 'file' ?
                        `<a href="javascript:void(0)" class="fb-name-link fb-name-text" data-action="download" data-id="${row.id}" title="Download">${row.name}</a>` :
                        `<span class="fb-name-text">${row.name}</span>`;
                    return `
                    <tr class="${clickable} ${selectedCls}" data-open-id="${row.id}">
                        <td>
                            <input type="checkbox" class="fb-row-check" data-id="${row.id}" ${checked}>
                        </td>
                        <td>
                            <div class="fb-name-cell">
                                <i class="fa-solid ${icon} ${iconCls}"></i>
                                ${nameHtml}
                            </div>
                        </td>
                        <td>${row.size}</td>
                        <td>${row.modified}</td>
                        <td>${row.creator}</td>
                        <td>
                            <div class="fb-row-actions">
                                <button type="button" class="${favBtnCls}" data-action="favorite" data-id="${row.id}">
                                    <i class="${starClass} fa-star"></i>
                                </button>
                                <button type="button" class="fb-row-btn" data-action="download" data-id="${row.id}" title="Download"><i class="fa-solid fa-download"></i></button>
                            </div>
                        </td>
                    </tr>
                `;
                }).join('');

                $('#fbListBody').html(html ||
                    '<tr><td colspan="6" class="text-center text-muted py-4">No folders found</td></tr>');
            }

            function renderGrid() {
                const rows = sortedRows();
                const html = rows.map((row) => {
                    const checked = selected.has(row.id) ? 'checked' : '';
                    const starClass = row.favorite ? 'fa-solid' : 'fa-regular';
                    const favBtnCls = row.favorite ? 'fb-row-btn fb-fav-active' : 'fb-row-btn';
                    const {
                        fa: icon,
                        cls: iconCls
                    } = getItemIcon(row);
                    const clickable = row.type === 'folder' ? 'fb-item-folder' : '';

                    const gridSelectedCls = selected.has(row.id) ? 'fb-row-selected' : '';
                    const gridNameHtml = row.type === 'file' ?
                        `<a href="javascript:void(0)" class="fb-name-link" data-action="download" data-id="${row.id}" title="Download">${row.name}</a>` :
                        row.name;
                    const gridIconHtml = row.type === 'file' ?
                        `<a href="javascript:void(0)" data-action="download" data-id="${row.id}" title="Download"><i class="fa-solid ${icon} ${iconCls}"></i></a>` :
                        `<i class="fa-solid ${icon} ${iconCls}"></i>`;
                    return `
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="fb-grid-card ${clickable} ${gridSelectedCls}" data-open-id="${row.id}">
                            <div class="fb-grid-top">
                                <label>
                                    <input type="checkbox" class="fb-row-check" data-id="${row.id}" ${checked}>
                                </label>
                                <div class="d-flex align-items-center gap-1">
                                    <button type="button" class="${favBtnCls}" data-action="favorite" data-id="${row.id}">
                                        <i class="${starClass} fa-star"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="fb-grid-icon text-center">
                                ${gridIconHtml}
                            </div>
                            <p class="fb-grid-name">${gridNameHtml}</p>
                            <p class="fb-grid-meta">Size: ${row.size}</p>
                            <p class="fb-grid-meta">Last Modified: ${row.modified}</p>
                            <p class="fb-grid-meta">Creator: ${row.creator}</p>

                            <div class="fb-row-actions mt-2">
                                <button type="button" class="fb-row-btn" data-action="download" data-id="${row.id}" title="Download"><i class="fa-solid fa-download"></i></button>
                            </div>
                        </div>
                    </div>
                `;
                }).join('');

                $('#fbGridBody').html(html ||
                    '<div class="col-12 text-center text-muted py-4">No folders found</div>');
            }

            function openFolder(id) {
                const target = currentItems().find((item) => item.id === id && item.type === 'folder');
                if (!target) {
                    return;
                }
                pathStack.push(target);
                setExpandedToCurrentPath();
                query = '';
                $('#fbSearchInput').val('');
                selected.clear();
                updateBreadcrumb();
                renderAll();
            }

            function renderAll() {
                renderList();
                renderGrid();
                updateSelectedCount();
                renderTree();
                savePathToUrl();

                $('#fbBulkDownloadBtn').show();
            }

            function updateSelectedCount() {
                const visibleIds = sortedRows().map((r) => r.id);
                const visibleSelected = visibleIds.filter((id) => selected.has(id)).length;
                $('#fbSelectedCount').text(`${visibleSelected} selected`);

                const allVisibleChecked = visibleIds.length > 0 && visibleSelected === visibleIds.length;
                $('#fbSelectAll').prop('checked', allVisibleChecked);
            }

            function closeMenus() {
                $('.fb-dropdown-menu.open').each(function() {
                    // Grid cards use a 3-dot trigger icon (fa-ellipsis-vertical)
                    // that never changes on open/close — only the list view's
                    // chevron-down/up flips, so only touch it if present.
                    const $icon = $(this).siblings('.fb-row-more-btn').find('i');
                    if ($icon.hasClass('fa-chevron-up')) {
                        $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                });
                $('.fb-dropdown-menu').removeClass('open');
                $('#fbListBody tr, .fb-grid-card').removeClass('fb-row-menu-open');
            }

            function collectAllSelectedIds() {
                return Array.from(selected).filter((id) => currentItems().find((item) => item.id === id));
            }

            $(document).on('click', '.fb-view-btn', function() {
                $('.fb-view-btn').removeClass('active');
                $(this).addClass('active');

                activeView = $(this).data('view');
                localStorage.setItem('fb_view_mode', activeView);

                if (activeView === 'list') {
                    $('#fbListView').removeClass('d-none');
                    $('#fbGridView').addClass('d-none');
                } else {
                    $('#fbGridView').removeClass('d-none');
                    $('#fbListView').addClass('d-none');
                }
            });

            function findNodeById(node, id) {
                if (String(node.id) === String(id)) return node;
                for (const child of (node.children || [])) {
                    const found = findNodeById(child, id);
                    if (found) return found;
                }
                return null;
            }

            function savePathToUrl() {
                const ids = pathStack.slice(1).map(n => n.id).join(',');
                history.replaceState(null, '', ids ? '#path=' + ids : location.pathname + location.search);
            }

            function restorePathFromUrl() {
                const hash = window.location.hash;
                if (!hash || !hash.startsWith('#path=')) return;
                const ids = hash.slice(6).split(',').filter(id => id && !isNaN(id));
                if (!ids.length) return;
                const newStack = [rootFolder];
                let searchBase = rootFolder;
                for (const id of ids) {
                    // Search only within children of the current node, never the node
                    // itself — avoids collision between Drive.id and Folder.id
                    let found = null;
                    for (const child of (searchBase.children || [])) {
                        found = findNodeById(child, Number(id));
                        if (found) break;
                    }
                    if (!found || found.type !== 'folder') break;
                    newStack.push(found);
                    searchBase = found;
                }
                if (newStack.length > 1) {
                    pathStack.splice(0, pathStack.length, ...newStack);
                    setExpandedToCurrentPath();
                }
            }

            $('#fbSearchInput').on('input', function() {
                query = $(this).val().trim();
                renderAll();
            });

            $(document).on('click', '.sortable', function() {
                // 3-click cycle per column: ascending → descending → default
                // (unsorted, original order) → ascending → ...
                const key = $(this).data('sort');
                if (activeSort.key !== key) {
                    activeSort = {
                        key,
                        direction: 'asc'
                    };
                } else if (activeSort.direction === 'asc') {
                    activeSort.direction = 'desc';
                } else if (activeSort.direction === 'desc') {
                    activeSort = {
                        key: null,
                        direction: 'asc'
                    };
                } else {
                    activeSort = {
                        key,
                        direction: 'asc'
                    };
                }
                renderAll();
            });

            $('#fbSelectAll').on('change', function() {
                const checked = $(this).is(':checked');
                const visibleIds = sortedRows().map((r) => r.id);

                visibleIds.forEach((id) => {
                    if (checked) {
                        selected.add(id);
                    } else {
                        selected.delete(id);
                    }
                });
                renderAll();
            });

            $(document).on('change', '.fb-row-check', function() {
                const id = Number($(this).data('id'));
                const isChecked = $(this).is(':checked');
                if (isChecked) {
                    selected.add(id);
                } else {
                    selected.delete(id);
                }
                // Sync all checkboxes for this id and highlight their row/card
                $('.fb-row-check[data-id="' + id + '"]').each(function() {
                    $(this).prop('checked', isChecked);
                    $(this).closest('tr, .fb-grid-card').toggleClass('fb-row-selected', isChecked);
                });
                updateSelectedCount();
            });

            $(document).on('click', '[data-open-id]', function(e) {
                if ($(e.target).closest('button, input, a, .fb-dropdown-menu').length) {
                    return;
                }
                const id = Number($(this).data('open-id'));
                openFolder(id);
            });

            $(document).on('click', '[data-tree-toggle]', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const key = String($(this).data('tree-toggle'));
                if (expandedTreeIds.has(key)) {
                    expandedTreeIds.delete(key);
                } else {
                    expandedTreeIds.add(key);
                }

                renderTree();
            });

            $(document).on('click', '.fb-tree-node', function(e) {
                if ($(e.target).closest('[data-tree-toggle]').length) {
                    return;
                }

                const key = String($(this).data('tree-key'));
                const type = $(this).data('tree-type');

                if (type !== 'folder') {
                    return;
                }

                const path = findFolderPath(rootFolder, key, [], true);
                if (!path) {
                    return;
                }

                pathStack.splice(0, pathStack.length, ...path);
                setExpandedToCurrentPath();
                openTreeMenu();
                query = '';
                $('#fbSearchInput').val('');
                selected.clear();
                updateBreadcrumb();
                renderAll();
            });

            // ── Progress-bar download (replaces window.open — avoids the
            // new-tab-redirects-then-downloads flow for large zips, and
            // shows real percentage progress off the response body). ──
            let activeDownloadXhr = null;

            function startDownload(url, suggestedName) {
                const $modal = $('#fbDownloadModal');
                const $bar = $('#fbDownloadProgressBar');
                const $percent = $('#fbDownloadPercent');
                const $title = $('#fbDownloadTitle');

                $title.text('Preparing download…');
                $bar.css('width', '0%');
                $percent.text('0%');
                $modal.removeClass('d-none');

                const xhr = new XMLHttpRequest();
                activeDownloadXhr = xhr;
                xhr.open('GET', url, true);
                xhr.responseType = 'blob';

                xhr.onprogress = function(e) {
                    $title.text('Downloading…');
                    if (e.lengthComputable) {
                        const pct = Math.round((e.loaded / e.total) * 100);
                        $bar.css('width', pct + '%');
                        $percent.text(pct + '%');
                    }
                };

                xhr.onload = function() {
                    activeDownloadXhr = null;

                    if (xhr.status < 200 || xhr.status >= 300) {
                        $modal.addClass('d-none');
                        alert('Download failed. Please try again.');
                        return;
                    }

                    $bar.css('width', '100%');
                    $percent.text('100%');

                    let filename = suggestedName || 'download';
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    if (disposition) {
                        const match = disposition.match(/filename\*?=(?:UTF-8'')?"?([^";]+)"?/i);
                        if (match && match[1]) {
                            filename = decodeURIComponent(match[1]);
                        }
                    }

                    const blobUrl = window.URL.createObjectURL(xhr.response);
                    const a = document.createElement('a');
                    a.href = blobUrl;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    setTimeout(() => window.URL.revokeObjectURL(blobUrl), 1000);

                    setTimeout(() => $modal.addClass('d-none'), 300);
                };

                xhr.onerror = function() {
                    activeDownloadXhr = null;
                    $modal.addClass('d-none');
                    alert('Download failed. Please try again.');
                };

                xhr.send();
            }

            $('#fbDownloadCancelBtn').on('click', function() {
                if (activeDownloadXhr) {
                    activeDownloadXhr.abort();
                    activeDownloadXhr = null;
                }
                $('#fbDownloadModal').addClass('d-none');
            });

            $('#fbBulkDownloadBtn').on('click', function() {
                const ids = collectAllSelectedIds();

                if (ids.length === 0) {
                    alert('Please select at least one item for bulk download.');
                    return;
                }

                const url = downloadMultipleUrl + '?ids=' + encodeURIComponent(ids.join(','));
                startDownload(url, 'download.zip');
            });

            $(document).on('click', '.fb-tree-download-trigger', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const id = Number($(this).data('id'));
                const type = $(this).data('type');
                const node = findNodeById(rootFolder, id);
                if (!node) return;

                const webUrl = '/files/' + btoa(String(id)) + '/download/' + type;
                startDownload(webUrl, node.name);
            });

            $(document).on('click', '[data-action="download"]', function() {
                const id = Number($(this).data('id'));

                const row = currentItems().find((item) => item.id === id);

                if (!row) {
                    return;
                }

                // A folder isn't a single file on disk — it has to go
                // through the zip-download endpoint (same one the toolbar's
                // Bulk Download button uses), not the single-file route.
                if (row.type === 'folder') {
                    const url = downloadMultipleUrl + '?ids=' + encodeURIComponent(String(row.id));
                    startDownload(url, row.name + '.zip');
                    return;
                }

                var webUrl = '/files/' + btoa(row.id) + '/download/' + row.type;

                startDownload(webUrl, row.name);

            });





            $(document).on('click', '[data-action="favorite"]', function() {
                const id = Number($(this).data('id'));
                const row = currentItems().find((item) => item.id === id);
                if (!row) return;

                $.ajax({
                    url: toggleFavoriteUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        id: row.id
                    },
                    success: function() {
                        row.favorite = !row.favorite;
                        renderAll();
                        showToast('Saved successfully', 'success');
                    },
                    error: function() {
                        alert('Failed to update favorite.');
                    }
                });
            });

            $('[data-action="download-current"]').on('click', function() {
                const current = currentFolder();

                if (!current || !current.id) {
                    alert('No folder selected.');
                    return;
                }

                const url = downloadMultipleUrl + '?ids=' + encodeURIComponent(String(current.id));
                startDownload(url, current.name + '.zip');
            });

            $('[data-action="favorite-current"]').on('click', function() {
                const ids = collectAllSelectedIds();

                if (ids.length === 0) {
                    alert('Please select at least one item to favorite.');
                    return;
                }

                let done = 0;
                let failed = 0;

                ids.forEach(function(id) {
                    const row = currentItems().find(item => item.id === id);
                    if (!row) {
                        done++;
                        return;
                    }

                    $.ajax({
                        url: toggleFavoriteUrl,
                        method: 'POST',
                        data: {
                            _token: csrfToken,
                            id: id
                        },
                        success: function() {
                            row.favorite = !row.favorite;
                        },
                        error: function() {
                            failed++;
                        },
                        complete: function() {
                            done++;
                            if (done === ids.length) {
                                renderAll();
                                if (failed > 0) {
                                    showToast(failed + ' item(s) failed to update.',
                                        'error');
                                } else {
                                    showToast('Favorite updated for ' + ids.length +
                                        ' item(s).', 'success');
                                }
                            }
                        }
                    });
                });
            });

            $(document).on('click', '.fb-more-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const menuId = $(this).data('dropdown');
                const $menu = $('#' + menuId);
                const opening = !$menu.hasClass('open');

                closeMenus();
                if (opening) {
                    $menu.addClass('open');
                }
            });

            // A fixed-position menu doesn't move with the page, so leaving
            // it open through a scroll/resize would visually detach it from
            // the button that opened it — simplest correct behavior is to
            // just close it, same as clicking outside does.
            $(window).on('scroll resize', function() {
                closeMenus();
            });

            $(document).on('click', function(e) {
                if ($(e.target).closest('.fb-dropdown').length === 0) {
                    closeMenus();
                }
            });

            $(document).on('click', '.fb-crumb[data-level="0"]', function(e) {
                e.preventDefault();
                pathStack.splice(1);
                setExpandedToCurrentPath();
                query = '';
                $('#fbSearchInput').val('');
                selected.clear();
                updateBreadcrumb();
                renderAll();
            });

            $(document).on('click', '.fb-path-crumb', function(e) {
                e.preventDefault();
                const absoluteLevel = Number($(this).data('level'));
                pathStack.splice(absoluteLevel);
                setExpandedToCurrentPath();
                query = '';
                $('#fbSearchInput').val('');
                selected.clear();
                updateBreadcrumb();
                renderAll();
            });

            $('#fbBackBtn').on('click', function() {
                if (pathStack.length <= 1) {
                    return;
                }

                pathStack.pop();
                setExpandedToCurrentPath();
                query = '';
                $('#fbSearchInput').val('');
                selected.clear();
                updateBreadcrumb();
                renderAll();
            });

            // Toggles the sidebar tree collapsed/expanded — the page/current
            // folder never changes, unlike _fbGoToRoot() which navigates
            // away. Collapsing remembers exactly which nodes were open so
            // toggling back restores the same tree state, not just the
            // current-path default.
            window._fbToggleTreeCollapse = function() {
                if (!treeCollapsed) {
                    savedExpandedTreeIds = new Set(expandedTreeIds);
                    expandedTreeIds.clear();
                    treeCollapsed = true;
                } else {
                    expandedTreeIds.clear();
                    if (savedExpandedTreeIds) {
                        savedExpandedTreeIds.forEach((id) => expandedTreeIds.add(id));
                    }
                    treeCollapsed = false;
                }

                renderTree();
                setTreeCollapseIcon(treeCollapsed);
            };

            window._fbGoToRoot = function() {
                pathStack.splice(1);
                setExpandedToCurrentPath();
                query = '';
                $('#fbSearchInput').val('');
                selected.clear();
                updateBreadcrumb();
                renderAll();
            };

            // Restore the last view mode (List/Grid) the user picked, so it
            // persists across page loads instead of always resetting to List.
            if (activeView === 'grid') {
                $('.fb-view-btn').removeClass('active');
                $('.fb-view-btn[data-view="grid"]').addClass('active');
                $('#fbGridView').removeClass('d-none');
                $('#fbListView').addClass('d-none');
            }

            restorePathFromUrl();
            updateBreadcrumb();

            // A search result link is a full page load — read back whatever
            // query was in progress so the tree lands here already filtered,
            // matching what the search box shows.
            treeSearchQuery = (sessionStorage.getItem('fb_tree_search_query') || '').trim().toLowerCase();

            renderAll();
        });
    </script>
@endpush
