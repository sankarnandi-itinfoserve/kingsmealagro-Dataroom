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
                        <button class="fb-tool-btn fb-tool-btn-primary" data-action="add-file-folder">
                            <i class="fa-solid fa-file-circle-plus"></i> Add File
                        </button>
                        <button class="fb-tool-btn fb-tool-btn-primary" data-action="add-folder-toolbar">
                            <i class="fa-solid fa-folder-plus"></i> Add Folder
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
            <!-- Upload Modal (iframe to reuse upload page) -->
            <div id="uploadModal" class="fb-modal d-none" aria-hidden="true">
                <div class="fb-modal-backdrop"></div>
                <div class="fb-modal-dialog">
                    <div class="fb-modal-header">
                        <h5 class="fb-modal-title">Upload to folder</h5>
                        <button type="button" class="fb-modal-close" id="uploadModalClose">&times;</button>
                    </div>
                    <div class="fb-modal-body">
                        <!-- Embedded upload UI -->
                        <div id="modalUpload" class="upload-card">
                            <p class="text-muted" style="margin-top:6px;">Drag & drop files here or click to select.
                                Multiple files supported.</p>

                            <div id="uploadDropzone" class="upload-dropzone" tabindex="0">
                                <div>
                                    <div style="font-weight:700">Drop files here</div>
                                    <div style="font-size:13px;color:#6b7280">or click to browse your computer</div>
                                </div>
                            </div>

                            <div class="upload-actions">
                                <input id="uploadInput" type="file" multiple style="display:none" />
                                <input id="uploadInputFolder" type="file" webkitdirectory directory multiple
                                    style="display:none" />
                                <button id="btnSelect" class="btn-clear">Select Files</button>
                                <button id="btnSelectFolder" class="btn-clear">Select Folder</button>
                                <button id="btnUpload" class="primary-btn">Upload All</button>
                                <button id="btnClear" class="btn-clear">Clear</button>
                                <div style="margin-left:auto;color:#475569;font-size:13px" id="totalCount">0 files</div>
                            </div>

                            <div id="uploadList" class="upload-list" aria-live="polite"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right-click Context Menu -->
    <!-- Create Folder Modal -->
    <div id="createFolderModal" class="fb-modal d-none" aria-hidden="true">
        <div class="fb-modal-backdrop"></div>
        <div class="cf-dialog">

            {{-- Header --}}
            <div class="cf-header">
                <div class="cf-header-icon">
                    <i class="fa-solid fa-folder-plus"></i>
                </div>
                <div class="cf-header-text">
                    <h5 class="cf-title">New Folder</h5>
                    <p class="cf-sub">Enter a name for the new folder.</p>
                </div>
                <button type="button" class="cf-close" id="createFolderModalClose" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            {{-- Body --}}
            <div class="cf-body">
                <label class="cf-label" for="newFolderName">Folder Name</label>
                <div class="cf-input-wrap">
                    <i class="fa-solid fa-folder cf-input-icon"></i>
                    <input type="text" id="newFolderName" class="cf-input" placeholder="e.g. Project Documents"
                        autocomplete="off" />
                </div>
            </div>

            {{-- Footer --}}
            <div class="cf-footer">
                <button type="button" class="cf-btn-cancel" id="createFolderCancel">Cancel</button>
                <button type="button" class="cf-btn-create" id="createFolderConfirm">
                    <i class="fa-solid fa-folder-plus"></i> Create Folder
                </button>
            </div>

        </div>
    </div>

    {{-- ── "Copy to..." / "Move to..." destination-picker modal — shared by
         both actions, only the title/icon/button label differ (set in JS) ── --}}
    <div id="copyItemModal" class="fb-modal d-none" aria-hidden="true">
        <div class="fb-modal-backdrop"></div>
        <div class="cf-dialog cp-dialog">

            <div class="cf-header">
                <div class="cf-header-icon" id="copyItemHeaderIcon">
                    <i class="fa-solid fa-copy"></i>
                </div>
                <div class="cf-header-text">
                    <h5 class="cf-title" id="copyItemTitle">Copy to&hellip;</h5>
                    <p class="cf-sub" id="copyItemSub">Choose a destination folder.</p>
                </div>
                <button type="button" class="cf-close" id="copyItemModalClose" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="cf-body cp-body">
                <div class="cp-search-wrap">
                    <i class="fa-solid fa-magnifying-glass cp-search-icon"></i>
                    <input type="text" id="copyItemSearch" class="cp-search-input" placeholder="Search folders…"
                        autocomplete="off">
                </div>
                <div class="cp-tree" id="copyItemTree"></div>
            </div>

            <div class="cf-footer">
                <button type="button" class="cf-btn-cancel" id="copyItemCancel">Cancel</button>
                <button type="button" class="cf-btn-create" id="copyItemConfirm" disabled>
                    <i class="fa-solid fa-copy"></i> Copy Here
                </button>
            </div>

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

        .fb-dropdown {
            position: relative;
        }

        .fb-row-more-btn {
            width: 26px;
            height: 26px;
            flex-shrink: 0;
            border: 1px solid transparent;
            border-radius: 6px;
            background: transparent;
            color: #94a3b8;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .12s, color .12s, border-color .12s;
        }

        .fb-row-more-btn:hover {
            background: #f1f5f9;
            color: #334155;
            border-color: #e2e8f0;
        }

        .fb-dropdown-menu {
            /* position/top/left are set inline in JS at open time, fixed to
                       the viewport — see the .fb-row-more-btn click handler. Using
                       position:fixed (not absolute-within-the-row) means the menu
                       can never end up rendered behind the toolbar/table header
                       above it, and can be flipped to open upward near the bottom
                       of the page without being clipped or hidden by anything. */
            position: fixed;
            min-width: 190px;
            padding: 6px;
            border: 1px solid #e9eef6;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 28px rgba(37, 52, 71, .13);
            display: none;
            z-index: 1050;
        }

        .fb-dropdown-menu.open {
            display: block;
            animation: fbDropdownIn .12s ease;
        }

        @keyframes fbDropdownIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fb-dropdown-menu button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            padding: 6px 8px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 500;
            color: #334155;
            cursor: pointer;
            transition: background .12s, color .12s;
        }

        .fb-dropdown-menu button:hover {
            background: #f1f5f9;
            color: #253447;
        }

        .fb-dropdown-menu .fb-dd-icon {
            width: 26px;
            height: 26px;
            min-width: 26px;
            border-radius: 7px;
            background: rgba(37, 52, 71, .07);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            color: #475569;
            flex-shrink: 0;
        }

        .fb-dropdown-menu .fb-dd-divider {
            margin: 4px 6px;
            border: 0;
            border-top: 1px solid #f1f5f9;
        }

        .fb-dropdown-menu button.fb-dd-danger {
            color: #dc2626;
        }

        .fb-dropdown-menu button.fb-dd-danger:hover {
            background: #fef2f2;
            color: #b91c1c;
        }

        .fb-dropdown-menu button.fb-dd-danger .fb-dd-icon {
            background: rgba(220, 38, 38, .1);
            color: #dc2626;
        }

        /* Inline rename — replaces the name in place, click outside (blur)
                   saves, Escape cancels, same convention as Windows Explorer. */
        .fb-inline-rename-input {
            flex: 1 1 auto;
            min-width: 80px;
            max-width: 320px;
            font-size: 13px;
            font-family: inherit;
            color: #1e293b;
            padding: 3px 7px;
            border: 1.5px solid #2563eb;
            border-radius: 6px;
            outline: none;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .12);
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
            /* Fits the widest case (favorite + download + view + delete —
                       4 × 30px buttons + 6px gaps + cell padding) with a little
                       breathing room, instead of leaving ~60-90px of dead space
                       after the left-aligned icons. */
            width: 170px;
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
                width: 160px;
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

        /* Delete button */
        .fb-row-btn[data-action="delete"]:hover {
            border-color: #fca5a5;
            background: #fef2f2;
            color: #dc2626;
        }

        /* Highlight checked rows — target td to override Bootstrap 5's cell-level bg */
        #fbListBody tr.fb-row-selected>td {
            background-color: #dbeafe !important;
        }

        #fbListBody tr.fb-row-selected:hover>td {
            background-color: #bfdbfe !important;
        }

        /* Row whose "more options" menu is currently open — same blue as
                   the checkbox-selected highlight. */
        #fbListBody tr.fb-row-menu-open>td {
            background-color: #dbeafe !important;
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
            const copyMultipleUrl = '{{ route('folders.mcopyMultiple') }}';
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

            function currentFolderCanAdd() {
                // Root-level project folders are only created via Projects
                // Management, never from this file browser — so Add File/Add
                // Folder never show on the top-level listing.
                return currentFolder().id !== rootFolder.id;
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
                    const showTreeView = !isFolder;
                    const treeNameHtml = showTreeView ?
                        `<a href="/files/${btoa(String(item.id))}/preview" class="fb-name-link" title="${item.name}">${item.name}</a>` :
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
                    // Only the type-based distinctions remain: "view" opens a
                    // file preview, "add" puts something inside a folder.
                    const showView = row.type === 'file';
                    const showAdd = row.type === 'folder';
                    const nameHtml = showView ?
                        `<a href="/files/${btoa(String(row.id))}/preview" class="fb-name-link fb-name-text">${row.name}</a>` :
                        `<span class="fb-name-text">${row.name}</span>`;
                    const rowMenuHtml = `
                        <div class="fb-dropdown fb-row-more ms-auto">
                            <button type="button" class="fb-row-more-btn" title="More options" data-id="${row.id}">
                                <i class="fa-solid fa-chevron-down"></i>
                            </button>
                            <div class="fb-dropdown-menu">
                                ${showAdd ? `<button type="button" data-action="row-add-file" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-file-arrow-up"></i></span><span>Add File</span></button>` : ''}
                                ${showAdd ? `<button type="button" data-action="row-add-folder" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-folder-plus"></i></span><span>Add Folder</span></button>` : ''}
                                <button type="button" data-action="download" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-download"></i></span><span>Download</span></button>
                                <button type="button" data-action="copy" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-copy"></i></span><span>Copy</span></button>
                                <button type="button" data-action="rename" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-pen"></i></span><span>Rename</span></button>
                                <button type="button" data-action="move" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-arrows-up-down-left-right"></i></span><span>Move</span></button>
                                <button type="button" class="fb-dd-danger" data-action="delete" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-trash-can"></i></span><span>Delete</span></button>
                            </div>
                        </div>`;

                    return `
                    <tr class="${clickable} ${selectedCls}" data-open-id="${row.id}">
                        <td>
                            <input type="checkbox" class="fb-row-check" data-id="${row.id}" ${checked}>
                        </td>
                        <td>
                            <div class="fb-name-cell">
                                <i class="fa-solid ${icon} ${iconCls}"></i>
                                ${nameHtml}
                                ${rowMenuHtml}
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
                                <button type="button" class="fb-row-btn" data-action="delete" data-id="${row.id}" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                ${showView ? `<button type="button" class="fb-row-btn" data-action="view" data-id="${row.id}" title="View"><i class="fa-solid fa-eye"></i></button>` : ''}
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
                    const showView = row.type === 'file';
                    const showAdd = row.type === 'folder';
                    const gridMenuHtml = `
                        <div class="fb-dropdown fb-row-more">
                            <button type="button" class="fb-row-more-btn" title="More options" data-id="${row.id}">
                                <i class="fa-solid fa-ellipsis-vertical"></i>
                            </button>
                            <div class="fb-dropdown-menu">
                                ${showAdd ? `<button type="button" data-action="row-add-file" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-file-arrow-up"></i></span><span>Add File</span></button>` : ''}
                                ${showAdd ? `<button type="button" data-action="row-add-folder" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-folder-plus"></i></span><span>Add Folder</span></button>` : ''}
                                <button type="button" data-action="download" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-download"></i></span><span>Download</span></button>
                                <button type="button" data-action="copy" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-copy"></i></span><span>Copy</span></button>
                                <button type="button" data-action="rename" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-pen"></i></span><span>Rename</span></button>
                                <button type="button" data-action="move" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-arrows-up-down-left-right"></i></span><span>Move</span></button>
                                <button type="button" class="fb-dd-danger" data-action="delete" data-id="${row.id}"><span class="fb-dd-icon"><i class="fa-solid fa-trash-can"></i></span><span>Delete</span></button>
                            </div>
                        </div>`;
                    const gridNameHtml = showView ?
                        `<a href="/files/${btoa(String(row.id))}/preview" class="fb-name-link">${row.name}</a>` :
                        row.name;
                    const gridIconHtml = showView ?
                        `<a href="/files/${btoa(String(row.id))}/preview"><i class="fa-solid ${icon} ${iconCls}"></i></a>` :
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
                                    ${gridMenuHtml}
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
                                <button type="button" class="fb-row-btn" data-action="delete" data-id="${row.id}" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                ${showView ? `<button type="button" class="fb-row-btn" data-action="view" data-id="${row.id}" title="View"><i class="fa-solid fa-eye"></i></button>` : ''}
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

                // Add File/Add Folder don't apply to the top-level listing.
                const canAdd = currentFolderCanAdd();
                $('[data-action="add-file-folder"]').toggle(canAdd);
                $('[data-action="add-folder-toolbar"]').toggle(canAdd);
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

            function humanSize(bytes) {
                if (!bytes || bytes === 0) return '0 B';
                const units = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return (bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0) + ' ' + units[i];
            }

            function formatModified(d) {
                const mm = String(d.getMonth() + 1).padStart(2, '0');
                const dd = String(d.getDate()).padStart(2, '0');
                const yy = d.getFullYear();
                const hh = String(d.getHours() % 12 || 12).padStart(2, '0');
                const min = String(d.getMinutes()).padStart(2, '0');
                const ap = d.getHours() < 12 ? 'AM' : 'PM';
                return `${mm}/${dd}/${yy} ${hh}:${min} ${ap}`;
            }

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

            function bubbleSizeUp(fromId, addedBytes) {
                function walk(node) {
                    if (String(node.id) === String(fromId)) {
                        node.sizeValue = (node.sizeValue || 0) + addedBytes;
                        node.size = humanSize(node.sizeValue);
                        return true;
                    }
                    for (const child of (node.children || [])) {
                        if (walk(child)) {
                            node.sizeValue = (node.sizeValue || 0) + addedBytes;
                            node.size = humanSize(node.sizeValue);
                            return true;
                        }
                    }
                    return false;
                }
                walk(rootFolder);
            }

            function injectNodes(nodes, parentId) {
                const target = findNodeById(rootFolder, parentId);
                if (!target) return;
                nodes.forEach(n => {
                    const exists = (target.children || []).some(c => String(c.id) === String(n.id));
                    if (!exists) {
                        target.children = target.children || [];
                        target.children.push(n);
                        bubbleSizeUp(parentId, n.sizeValue || 0);
                    }
                });
                renderAll();
            }

            // upload modal handlers (inline uploader)
            let modalUploadParentId = null;
            let modalUploadIsRoot = false;
            let modalFiles = [];

            function resetModalUploader() {
                modalFiles = [];
                $('#uploadList').empty();
                $('#totalCount').text('0 files');
                $('#btnUpload').prop('disabled', false);
            }

            function openUploadModal(parentId, isRoot) {
                modalUploadParentId = parentId || '';
                modalUploadIsRoot = !!isRoot;
                resetModalUploader();
                $('#uploadModal').removeClass('d-none').attr('aria-hidden', 'false');
                $('body').css('overflow', 'hidden');
            }

            function closeUploadModal() {
                resetModalUploader();
                modalUploadParentId = null;
                modalUploadIsRoot = false;
                $('#uploadModal').addClass('d-none').attr('aria-hidden', 'true');
                $('body').css('overflow', '');
            }

            $(document).on('click', '[data-action="upload"]', function(e) {
                e.preventDefault();
                const id = $(this).data('id');
                openUploadModal(id);
            });

            $(document).on('click', '[data-action="add-file-folder"]', function(e) {
                e.preventDefault();
                const isRoot = pathStack.length === 1;
                const id = currentFolder() && currentFolder().id ? currentFolder().id : '';
                openUploadModal(id, isRoot);
            });

            $(document).on('click', '[data-action="add-folder-toolbar"]', function(e) {
                e.preventDefault();
                const isRoot = pathStack.length === 1;
                const id = currentFolder() && currentFolder().id ? currentFolder().id : '';
                openCreateFolderModal(id, isRoot);
            });

            $('#uploadModalClose, .fb-modal-backdrop').on('click', function() {
                closeUploadModal();
            });

            // embedded uploader logic
            (function() {
                const drop = document.getElementById('uploadDropzone');
                const input = document.getElementById('uploadInput');
                const folderInput = document.getElementById('uploadInputFolder');
                const btnSelect = document.getElementById('btnSelect');
                const btnSelectFolder = document.getElementById('btnSelectFolder');
                const btnUpload = document.getElementById('btnUpload');
                const btnClear = document.getElementById('btnClear');
                const listEl = document.getElementById('uploadList');
                const totalCountEl = document.getElementById('totalCount');

                function renderList() {
                    listEl.innerHTML = '';
                    modalFiles.forEach(item => {
                        const id = item.id;
                        const f = item.file;
                        const el = document.createElement('div');
                        el.className = 'upload-item';

                        const thumb = document.createElement('div');
                        thumb.className = 'upload-thumb';

                        if (f.type && f.type.startsWith('image/')) {
                            const img = document.createElement('img');
                            img.src = URL.createObjectURL(f);
                            img.style.width = '100%';
                            img.style.height = '100%';
                            img.style.objectFit = 'cover';
                            thumb.appendChild(img);
                        } else {
                            thumb.innerHTML =
                                '<i class="fa-solid fa-file" style="color:#64748b;font-size:18px"></i>';
                        }

                        const meta = document.createElement('div');
                        meta.className = 'upload-meta';
                        meta.innerHTML =
                            `<div class="name">${f.name}</div><div class="size">${humanSize(f.size)}</div><div class="upload-progress"><i style="width:${item.progress || 0}%"></i></div>`;

                        const actions = document.createElement('div');
                        actions.className = 'actions';

                        const removeBtn = document.createElement('button');
                        if (item.progress >= 100) {
                            removeBtn.className = 'upload-done';
                            removeBtn.textContent = 'Uploaded';
                            removeBtn.disabled = true;
                        } else {
                            removeBtn.className = 'upload-remove';
                            removeBtn.textContent = 'Remove';
                            removeBtn.addEventListener('click', () => {
                                modalFiles = modalFiles.filter(x => x.id !== id);
                                renderList();
                                updateCount();
                            });
                        }

                        actions.appendChild(removeBtn);

                        el.appendChild(thumb);
                        el.appendChild(meta);
                        el.appendChild(actions);

                        listEl.appendChild(el);
                    });
                }

                function updateCount() {
                    totalCountEl.textContent = `${modalFiles.length} file${modalFiles.length!==1?'s':''}`;
                }

                function addFiles(fileList) {
                    const start = modalFiles.length ? modalFiles[modalFiles.length - 1].id + 1 : 1;
                    Array.from(fileList).forEach((f, i) => {
                        modalFiles.push({
                            id: start + i,
                            file: f,
                            progress: 0
                        });
                    });
                    renderList();
                    updateCount();
                }

                drop && drop.addEventListener('click', () => input.click());
                btnSelect && btnSelect.addEventListener('click', (e) => {
                    e.preventDefault();
                    input.click();
                });

                btnSelectFolder && btnSelectFolder.addEventListener('click', (e) => {
                    e.preventDefault();
                    folderInput && folderInput.click();
                });

                input && input.addEventListener('change', (e) => {
                    if (e.target.files && e.target.files.length) {
                        addFiles(e.target.files);
                        e.target.value = '';
                    }
                });

                folderInput && folderInput.addEventListener('change', (e) => {
                    if (e.target.files && e.target.files.length) {
                        addFiles(e.target.files);
                        e.target.value = '';
                    }
                });

                drop && drop.addEventListener('dragenter', (e) => {
                    e.preventDefault();
                    drop.classList.add('dragover');
                });
                drop && drop.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    drop.classList.add('dragover');
                });
                drop && drop.addEventListener('dragleave', (e) => {
                    e.preventDefault();
                    drop.classList.remove('dragover');
                });
                drop && drop.addEventListener('drop', (e) => {
                    e.preventDefault();
                    drop.classList.remove('dragover');
                    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                        addFiles(e.dataTransfer.files);
                    }
                });

                btnClear && btnClear.addEventListener('click', (e) => {
                    e.preventDefault();
                    modalFiles = [];
                    renderList();
                    updateCount();
                });

                function uploadOne(item) {
                    return new Promise((resolve) => {
                        const xhr = new XMLHttpRequest();
                        const fd = new FormData();
                        fd.append('file', item.file);
                        if (modalUploadParentId) fd.append('parent_id', modalUploadParentId);
                        if (modalUploadIsRoot) fd.append('parent_type', 'drive');
                        const url = '/files/store';
                        xhr.open('POST', url, true);
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content');
                        if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token);

                        xhr.upload.onprogress = function(e) {
                            if (e.lengthComputable) {
                                const pct = Math.round((e.loaded / e.total) * 100);
                                item.progress = pct;
                                renderList();
                            }
                        };

                        xhr.onload = function() {
                            item.progress = 100;
                            renderList();
                            resolve({
                                status: xhr.status,
                                response: xhr.responseText
                            });
                        };
                        xhr.onerror = function() {
                            item.progress = 0;
                            renderList();
                            resolve({
                                status: xhr.status || 500
                            });
                        };
                        xhr.send(fd);
                    });
                }

                async function uploadAll() {
                    if (!modalFiles.length) return;
                    btnUpload.disabled = true;
                    const uploadResults = [];
                    for (const item of modalFiles) {
                        if (item.progress >= 100) continue;
                        const res = await uploadOne(item);
                        if (res) uploadResults.push({
                            item,
                            res
                        });
                    }
                    btnUpload.disabled = false;

                    // Inject newly uploaded files into the tree without page reload
                    uploadResults.forEach(({
                        item,
                        res
                    }) => {
                        try {
                            const data = JSON.parse(res.response);
                            const parentId = data.parent_db_id || modalUploadParentId;
                            const now = new Date();
                            (data.results || []).forEach(r => {
                                if (r.folder_id && r.response && r.status >= 200 && r
                                    .status < 300) {
                                    const sp = r.response;
                                    const name = sp.name || item.file.name || '';
                                    const ext = name.includes('.') ? name.split('.').pop()
                                        .toLowerCase() : '';
                                    const fileSize = sp.size || item.file.size || 0;
                                    injectNodes([{
                                        id: r.folder_id,
                                        type: 'file',
                                        ext: ext,
                                        name: name,
                                        parentId: sp.parentReference ? sp
                                            .parentReference.id : null,
                                        size: humanSize(fileSize),
                                        sizeValue: fileSize,
                                        modified: formatModified(now),
                                        modifiedTs: Math.floor(now.getTime() /
                                            1000),
                                        creator: '',
                                        favorite: false,
                                        downloadUrl: sp[
                                            '@microsoft.graph.downloadUrl'
                                        ] || null,
                                        webUrl: sp.webUrl || null,
                                        children: [],
                                    }], parentId);
                                }
                            });
                        } catch (e) {}
                    });

                    setTimeout(() => {
                        $('#uploadModal').addClass('d-none').attr('aria-hidden', 'true');
                        modalFiles = [];
                        renderList();
                        updateCount();
                        showToast('All files uploaded successfully', 'success');
                    }, 800);
                }

                btnUpload && btnUpload.addEventListener('click', (e) => {
                    e.preventDefault();
                    uploadAll();
                });

                // export for debugging
                window._fbModalUpload = {
                    addFiles: addFiles,
                    files: modalFiles
                };
            })();

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

            $('#fbBulkDownloadBtn').on('click', function() {
                const ids = collectAllSelectedIds();

                if (ids.length === 0) {
                    alert('Please select at least one item for bulk download.');
                    return;
                }

                const url = downloadMultipleUrl + '?ids=' + encodeURIComponent(ids.join(','));
                window.open(url, '_blank');
            });

            function deleteDriveItem(id, name) {
                Swal.fire({
                    title: 'Delete Item?',
                    html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash"></i></div>"<strong>' +
                        name + '</strong>" will be permanently removed.',
                    width: '380px',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-theme'
                    },
                    reverseButtons: true,
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    $.ajax({
                        url: '/folders/' + id,
                        method: 'POST',
                        data: {
                            _token: csrfToken,
                            _method: 'DELETE'
                        },
                        success: function() {
                            // Remove from the in-memory tree
                            const parent = currentFolder();
                            if (parent.children) {
                                parent.children = parent.children.filter(c => c.id !== id);
                            }
                            selected.delete(id);
                            renderAll();
                            showToast('Deleted successfully', 'success');
                        },
                        error: function() {
                            showToast('Failed to delete item.', 'danger');
                        }
                    });
                });
            }

            $(document).on('click', '[data-action="delete"]', function() {
                const id = Number($(this).data('id'));
                const row = currentItems().find(item => item.id === id);
                if (!row) return;
                deleteDriveItem(id, row.name);
            });

            $(document).on('click', '[data-action="view"]', function() {
                const id = Number($(this).data('id'));
                const row = currentItems().find((item) => item.id === id);

                if (!row || !row.webUrl) {
                    alert('View not available for this item.');
                    return;
                }

                var webUrl = '/files/' + btoa(row.id) + '/preview';

                window.open(webUrl, '_blank');
            });

            $(document).on('click', '[data-action="download"]', function() {
                const id = Number($(this).data('id'));

                const row = currentItems().find((item) => item.id === id);

                if (!row) {
                    return;
                }

                var webUrl = '/files/' + btoa(row.id) + '/download/' + row.type;

                window.open(webUrl, '_blank');

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
                window.open(url, '_blank');
            });

            $('[data-action="copy-current"]').on('click', function() {
                const current = currentFolder();

                if (!current || !current.id) {
                    alert('No folder selected.');
                    return;
                }

                $.ajax({
                    url: copyMultipleUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        ids: [current.id]
                    },
                    success: function(res) {
                        alert(res.message || 'Folder copied successfully.');
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Failed to copy folder.');
                    }
                });
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

            {{-- Row-level "more" dropdown (Copy/Rename/Move) — one per row,
                 targeted via its own .fb-dropdown wrapper rather than an id,
                 since every row renders the same markup. --}}
            $(document).on('click', '.fb-row-more-btn', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $menu = $(this).siblings('.fb-dropdown-menu');
                const opening = !$menu.hasClass('open');

                closeMenus();
                if (opening) {
                    $menu.addClass('open');
                    const $icon = $(this).find('i');
                    if ($icon.hasClass('fa-chevron-down')) {
                        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    }
                    $(this).closest('tr, .fb-grid-card').addClass('fb-row-menu-open');

                    // Positioned fixed to the viewport (not absolute within
                    // the row) so it always renders above every other page
                    // element and can never end up clipped/hidden behind the
                    // toolbar or table header. Flips upward when the menu
                    // wouldn't fully fit below the button, so every option is
                    // visible right away without needing to scroll first.
                    const btnRect = this.getBoundingClientRect();
                    const menuWidth = $menu.outerWidth();
                    const menuHeight = $menu.outerHeight();

                    const fitsBelow = btnRect.bottom + 8 + menuHeight <= window.innerHeight;
                    const top = fitsBelow ?
                        btnRect.bottom + 8 :
                        Math.max(8, btnRect.top - 8 - menuHeight);
                    const left = Math.max(8, Math.min(
                        btnRect.right - menuWidth,
                        window.innerWidth - menuWidth - 8
                    ));

                    $menu.css({
                        top: top + 'px',
                        left: left + 'px'
                    });
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

            // ── Row "more" dropdown actions (Add File / Add Folder) — Download
            //    reuses the same [data-action="download"] handler the Actions
            //    column button already uses. Copy/Rename/Move aren't wired up
            //    yet (static only). ──────────────────────────────────────────
            $(document).on('click', '[data-action="row-add-file"]', function(e) {
                e.stopPropagation();
                closeMenus();
                const id = Number($(this).data('id'));
                const row = currentItems().find(item => item.id === id);
                if (!row) return;
                openUploadModal(row.id, false);
            });

            $(document).on('click', '[data-action="row-add-folder"]', function(e) {
                e.stopPropagation();
                closeMenus();
                const id = Number($(this).data('id'));
                const row = currentItems().find(item => item.id === id);
                if (!row) return;
                openCreateFolderModal(row.id);
            });

            // ── Create Folder modal ───────────────────────────────────────────
            const createFolderUrl = '{{ route('folders.store') }}';
            let createFolderParentId = null;
            let createFolderIsRoot = false;

            function openCreateFolderModal(parentId, isRoot) {
                createFolderParentId = parentId || '';
                createFolderIsRoot = !!isRoot;
                $('#newFolderName').val('');
                $('#createFolderModal').removeClass('d-none').attr('aria-hidden', 'false');
                $('body').css('overflow', 'hidden');
                setTimeout(() => $('#newFolderName').focus(), 80);
            }

            function closeCreateFolderModal() {
                createFolderParentId = null;
                createFolderIsRoot = false;
                $('#createFolderModal').addClass('d-none').attr('aria-hidden', 'true');
                $('body').css('overflow', '');
            }

            $('#createFolderModalClose, #createFolderCancel').on('click', closeCreateFolderModal);
            $('#createFolderModal .fb-modal-backdrop').on('click', closeCreateFolderModal);

            $('#createFolderConfirm').on('click', function() {
                const name = $('#newFolderName').val().trim();
                if (!name) {
                    $('#newFolderName').focus();
                    return;
                }

                $(this).prop('disabled', true).html(
                    '<i class="fa-solid fa-spinner fa-spin me-1"></i> Creating…');

                const savedParentId = createFolderParentId;
                const savedIsRoot = createFolderIsRoot;
                $.ajax({
                    url: createFolderUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        name: name,
                        parent_id: createFolderParentId || '',
                        parent_type: createFolderIsRoot ? 'drive' : 'folder',
                    },
                    success: function(res) {
                        closeCreateFolderModal();
                        showToast('Folder created successfully', 'success');
                        if (res.folder && savedParentId) {
                            injectNodes([res.folder], savedParentId);
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Failed to create folder.');
                    },
                    complete: function() {
                        $('#createFolderConfirm').prop('disabled', false).html(
                            '<i class="fa-solid fa-folder-plus"></i> Create Folder');
                    }
                });
            });

            // Allow Enter key to submit the folder name
            $('#newFolderName').on('keydown', function(e) {
                if (e.key === 'Enter') $('#createFolderConfirm').trigger('click');
            });

            // ── Inline rename (click outside / blur saves, Escape cancels —
            //    same convention as Windows Explorer) ──────────────────────────
            const renameItemUrl = '{{ route('folders.mrename') }}';
            let renamingId = null; // guards against starting a second inline edit at once

            function startInlineRename(id, row, $trigger) {
                if (renamingId !== null) return;
                const $nameCell = $trigger.closest('tr').find('.fb-name-cell');
                const $nameText = $nameCell.find('.fb-name-text');
                if (!$nameText.length) return;

                closeMenus();
                renamingId = id;
                const originalName = row.name;

                const $input = $(
                        '<input type="text" class="fb-inline-rename-input" autocomplete="off" spellcheck="false">')
                    .val(originalName);
                $nameText.hide();
                $input.insertAfter($nameText);
                $input.trigger('focus');

                // Pre-select just the base name (not the extension) for
                // files — matches Windows Explorer's rename convention.
                const dot = row.type === 'file' ? originalName.lastIndexOf('.') : -1;
                if (dot > 0) {
                    $input[0].setSelectionRange(0, dot);
                } else {
                    $input.trigger('select');
                }

                let settled = false;

                function finish(shouldSave) {
                    if (settled) return;
                    settled = true;
                    renamingId = null;
                    const newName = $input.val().trim();
                    $input.remove();
                    $nameText.show();

                    if (!shouldSave || !newName || newName === originalName) {
                        return;
                    }

                    $.ajax({
                        url: renameItemUrl,
                        method: 'POST',
                        data: {
                            _token: csrfToken,
                            id: id,
                            name: newName
                        },
                        success: function(res) {
                            showToast('Renamed successfully', 'success');
                            const node = findNodeById(rootFolder, id);
                            if (node) node.name = res.folder?.name ?? newName;
                            renderAll();
                        },
                        error: function(xhr) {
                            showToast(xhr.responseJSON?.message || 'Failed to rename.', 'danger');
                            renderAll(); // revert the row back to its original name
                        }
                    });
                }

                $input.on('blur', function() {
                    finish(true);
                });
                $input.on('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        $input.trigger('blur');
                    } else if (e.key === 'Escape') {
                        e.preventDefault();
                        finish(false);
                    }
                });
            }

            $(document).on('click', '[data-action="rename"]', function(e) {
                e.stopPropagation();
                const id = Number($(this).data('id'));
                const row = currentItems().find((item) => item.id === id);
                if (!row) return;
                startInlineRename(id, row, $(this));
            });

            // ── "Copy to..." / "Move to..." destination picker — one shared
            //    modal/tree for both actions, distinguished by pickerAction ──
            const copyItemUrl = '{{ route('folders.copyItem') }}';
            const moveItemUrl = '{{ route('folders.moveItem') }}';
            let copySourceId = null;
            let copySelectedDestId = null; // set once the user picks a destination folder in the tree
            let pickerAction = 'copy'; // 'copy' | 'move'
            let pickerExcludedIds = new Set(); // self + (for folders) every descendant folder id

            const PICKER_LABELS = {
                copy: {
                    icon: 'fa-copy',
                    title: 'Copy to&hellip;',
                    btn: 'Copy Here',
                    verb: 'Copying',
                    done: 'Copied successfully',
                    fail: 'Failed to copy item.'
                },
                move: {
                    icon: 'fa-arrows-up-down-left-right',
                    title: 'Move to&hellip;',
                    btn: 'Move Here',
                    verb: 'Moving',
                    done: 'Moved successfully',
                    fail: 'Failed to move item.'
                },
            };

            function collectDescendantFolderIds(node, into) {
                (node.children || []).forEach((child) => {
                    if (child.type !== 'folder') return;
                    into.add(child.id);
                    collectDescendantFolderIds(child, into);
                });
                return into;
            }

            // A node "matches" a search if its own name does, or any
            // descendant (folder or file, any depth) does — lets a query
            // find a deeply-nested folder, or a file that hints at which
            // folder to pick, without navigating the tree by hand.
            function cpNodeMatchesQuery(node, q) {
                if (node.name.toLowerCase().includes(q)) return true;
                return (node.children || []).some(child => cpNodeMatchesQuery(child, q));
            }

            function buildCopyPickerRows(node, query) {
                const q = (query || '').trim().toLowerCase();
                // No search: folders only, collapsed (original behavior).
                // While searching: files are considered too (shown for
                // context, never selectable), and only items that match —
                // by their own name or a matching descendant — are shown.
                const candidates = (node.children || []).filter(c => q ? true : c.type === 'folder');
                const visible = candidates.filter(c => !q || cpNodeMatchesQuery(c, q));
                if (!visible.length) return '';

                return visible.map((item) => {
                    if (item.type === 'file') {
                        return `
                            <div class="cp-node">
                                <div class="cp-item cp-disabled cp-file-row">
                                    <span class="cp-spacer"></span>
                                    <i class="fa-solid fa-file cp-file-icon"></i>
                                    <span class="cp-name">${item.name}</span>
                                </div>
                            </div>
                        `;
                    }

                    const childHtml = buildCopyPickerRows(item, q);
                    const hasChildren = childHtml.length > 0;
                    // Can't target the item itself, or — when moving a folder —
                    // any of its own sub-folders (that would be moving it inside
                    // its own descendant, which would orphan the sub-tree).
                    const disabled = pickerExcludedIds.has(item.id);
                    const selfMatches = q && item.name.toLowerCase().includes(q);
                    const selected = item.id === copySelectedDestId;

                    return `
                        <div class="cp-node${(q && hasChildren) || selected ? ' open' : ''}" data-cp-id="${item.id}">
                            <div class="cp-item${disabled ? ' cp-disabled' : ''}${selfMatches ? ' cp-match' : ''}${selected ? ' cp-selected' : ''}" data-cp-select="${item.id}">
                                ${hasChildren
                                    ? `<button type="button" class="cp-toggle" data-cp-toggle="${item.id}"><i class="fa-solid fa-chevron-right"></i></button>`
                                    : `<span class="cp-spacer"></span>`}
                                <i class="fa-solid fa-folder cp-folder-icon"></i>
                                <span class="cp-name">${item.name}</span>
                            </div>
                            ${hasChildren ? `<div class="cp-children">${childHtml}</div>` : ''}
                        </div>
                    `;
                }).join('');
            }

            function renderCopyPickerTree(query) {
                const q = (query || '').trim().toLowerCase();
                const rows = buildCopyPickerRows(rootFolder, q);
                $('#copyItemTree').html(rows || '<p class="cp-empty">No matching folders found.</p>');
            }

            function openCopyItemModal(id, name, action) {
                copySourceId = id;
                copySelectedDestId = null;
                pickerAction = action;

                const sourceNode = findNodeById(rootFolder, id);
                pickerExcludedIds = new Set([id]);
                if (sourceNode && sourceNode.type === 'folder') {
                    collectDescendantFolderIds(sourceNode, pickerExcludedIds);
                }

                const labels = PICKER_LABELS[action];
                $('#copyItemHeaderIcon i').attr('class', 'fa-solid ' + labels.icon);
                $('#copyItemTitle').html(labels.title);
                $('#copyItemSub').text(`Choose a destination for "${name}".`);
                $('#copyItemSearch').val('');
                renderCopyPickerTree('');
                $('#copyItemConfirm').prop('disabled', true).html(
                    `<i class="fa-solid ${labels.icon}"></i> ${labels.btn}`);

                $('#copyItemModal').removeClass('d-none').attr('aria-hidden', 'false');
                $('body').css('overflow', 'hidden');
            }

            $('#copyItemSearch').on('input', function() {
                renderCopyPickerTree($(this).val());
            });

            function closeCopyItemModal() {
                copySourceId = null;
                $('#copyItemModal').addClass('d-none').attr('aria-hidden', 'true');
                $('body').css('overflow', '');
            }

            $('#copyItemModalClose, #copyItemCancel').on('click', closeCopyItemModal);
            $('#copyItemModal .fb-modal-backdrop').on('click', closeCopyItemModal);

            $('#copyItemTree').on('click', '.cp-toggle', function(e) {
                e.stopPropagation();
                $(this).closest('.cp-node').toggleClass('open');
            });

            $('#copyItemTree').on('click', '[data-cp-select]', function() {
                if ($(this).hasClass('cp-disabled')) return;
                $('#copyItemTree .cp-item').removeClass('cp-selected');
                $(this).addClass('cp-selected');
                copySelectedDestId = $(this).data('cp-select');
                $('#copyItemConfirm').prop('disabled', false);
            });

            $(document).on('click', '[data-action="copy"], [data-action="move"]', function(e) {
                e.stopPropagation();
                closeMenus();
                const id = Number($(this).data('id'));
                const row = currentItems().find((item) => item.id === id);
                if (!row) return;
                openCopyItemModal(id, row.name, $(this).data('action'));
            });

            // Radio-button choice shown only when the destination already has
            // an item with the same name — Overwrite replaces it (and every
            // child underneath it) outright, Create New keeps both by
            // auto-suffixing " (Copy)", "(Copy)(Copy)", ... until it's unique.
            function showCopyConflictDialog(existingName) {
                return Swal.fire({
                    title: 'Item already exists',
                    html: `
                        <p style="margin:0 0 14px;font-size:13.5px;color:#475569;">
                            "<strong>${existingName}</strong>" already exists in this location.
                        </p>
                        <div style="display:flex;flex-direction:column;gap:10px;text-align:left;">
                            <label style="display:flex;align-items:center;gap:9px;cursor:pointer;">
                                <input type="radio" name="cpConflict" value="overwrite" checked>
                                <span>Overwrite &mdash; replace it (and everything inside it)</span>
                            </label>
                            <label style="display:flex;align-items:center;gap:9px;cursor:pointer;">
                                <input type="radio" name="cpConflict" value="rename">
                                <span>Create New &mdash; keep both, add "(Copy)" to the name</span>
                            </label>
                        </div>
                    `,
                    width: '420px',
                    showCancelButton: true,
                    confirmButtonColor: '#253447',
                    confirmButtonText: 'Continue',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-theme'
                    },
                    reverseButtons: true,
                    preConfirm: () => document.querySelector('input[name="cpConflict"]:checked').value,
                }).then((result) => result.isConfirmed ? result.value : null);
            }

            // Splices the moved node out of its old parent's children and
            // into the new one — the subtree it carries (already loaded
            // client-side) doesn't need re-fetching, only its own position
            // in the tree changes.
            function moveNodeInTree(id, oldParentId, newParentId, newName) {
                const oldParent = findNodeById(rootFolder, oldParentId ?? rootFolder.id);
                const newParent = findNodeById(rootFolder, newParentId ?? rootFolder.id);
                if (!oldParent || !newParent) {
                    renderAll();
                    return;
                }

                const idx = (oldParent.children || []).findIndex(c => c.id === id);
                if (idx === -1) {
                    renderAll();
                    return;
                }

                const [node] = oldParent.children.splice(idx, 1);
                node.name = newName;
                newParent.children = newParent.children || [];
                newParent.children.push(node);
                selected.delete(id);
                renderAll();
            }

            function performPickerAction(sourceId, destId, conflictResolution) {
                const labels = PICKER_LABELS[pickerAction];
                const $btn = $('#copyItemConfirm');
                $btn.prop('disabled', true).html(
                    `<i class="fa-solid fa-spinner fa-spin me-1"></i> ${labels.verb}…`);

                $.ajax({
                    url: pickerAction === 'move' ? moveItemUrl : copyItemUrl,
                    method: 'POST',
                    data: {
                        _token: csrfToken,
                        id: sourceId,
                        destination_id: destId === 'root' ? '' : destId,
                        conflict_resolution: conflictResolution || '',
                    },
                    success: function(res) {
                        closeCopyItemModal();
                        showToast(labels.done, 'success');
                        if (pickerAction === 'move') {
                            moveNodeInTree(res.id, res.oldParentId, res.destinationId, res.name);
                        } else {
                            const targetParentId = res.destinationId ?? rootFolder.id;
                            injectNodes([res.item], targetParentId);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 409 && xhr.responseJSON?.conflict) {
                            showCopyConflictDialog(xhr.responseJSON.existingName).then((choice) => {
                                if (choice) {
                                    performPickerAction(sourceId, destId, choice);
                                } else {
                                    $btn.prop('disabled', false).html(
                                        `<i class="fa-solid ${labels.icon}"></i> ${labels.btn}`
                                    );
                                }
                            });
                            return;
                        }
                        showToast(xhr.responseJSON?.message || labels.fail, 'danger');
                        $btn.prop('disabled', false).html(
                            `<i class="fa-solid ${labels.icon}"></i> ${labels.btn}`);
                    }
                });
            }

            $('#copyItemConfirm').on('click', function() {
                if (!copySourceId || !copySelectedDestId) return;
                performPickerAction(copySourceId, copySelectedDestId, null);
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
