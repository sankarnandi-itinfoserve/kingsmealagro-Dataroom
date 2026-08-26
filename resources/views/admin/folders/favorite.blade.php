@extends('admin.layouts.app')
@section('title', 'Favorites')
@section('page_title', 'Favorites')

@section('content')

    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card shared-folders">

            <div class="fb-header-row">
                <div>
                    <nav class="fb-breadcrumb" aria-label="Breadcrumb">
                        <a href="#" class="fb-crumb" data-level="0">Favorites </a>
                        <span class="fb-crumb-sep">&gt;</span>
                        <span class="fb-crumb-current" id="fbCurrentFolder">Favorites</span>
                    </nav>
                </div>

                <div class="fb-header-actions">
                    <div class="fb-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="fbSearchInput" placeholder="Search folders...">
                    </div>

                    {{-- <div class="fb-view-toggle" role="group" aria-label="View mode">
                        <button type="button" class="fb-view-btn view-btn active" data-view="list">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <button type="button" class="fb-view-btn view-btn" data-view="grid">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                    </div> --}}

                    <div class="fb-view-toggle" role="group" aria-label="View mode">
                        <button type="button" class="fb-view-btn view-btn active" data-view="list">
                            <i class="fa-solid fa-list"></i>
                        </button>
                        <button type="button" class="fb-view-btn view-btn" data-view="grid">
                            <i class="fa-solid fa-table-cells-large"></i>
                        </button>
                    </div>
                </div>
            </div>

            <section class="fb-main">

                {{-- <div class="fb-subscription-row">
                    <label class="fb-subscribe-label">
                        Email me when a file is <span class="text-primary fw-semibold">Uploaded</span> to this folder
                    </label>
                </div> --}}

                <div class="fb-bulk-row">
                    <label class="fb-subscribe-label">
                        <input type="checkbox" id="selectAll">
                        Select all
                    </label>
                    <span class="fb-sel-badge" id="fbSelectedCount">0 selected</span>

                    <span class="fb-toolbar-sep"></span>

                    <button type="button" class="fb-tool-btn" id="unfavoriteSelectedBtn">
                        <i class="fa-solid fa-star-half-stroke"></i> Unfavorite Selected
                    </button>
                    <button type="button" class="fb-tool-btn" id="removeAllFavoritesBtn">
                        <i class="fa-solid fa-trash-can"></i> Remove All from Favorites
                    </button>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div id="listView">

                            <table class="table fb-table align-middle mb-0">
                                <!-- Table Head -->
                                <thead>
                                    <tr>
                                        <th class="fb-col-check"></th>
                                        <th>Name</th>
                                        <th style="width:100px;">Size</th>
                                        <th style="width:160px;">Last modified</th>
                                        <th style="width:140px;">Creator</th>
                                        <th style="width:130px;">Action</th>
                                    </tr>
                                </thead>
                                <!-- Table Body -->
                                <tbody id="fbListBody"></tbody>

                            </table>
                        </div>
                        <!-- GRID VIEW -->
                        <div id="gridView" class="d-none">
                            <div class="row g-3" id="fbGridBody"></div>
                        </div>

                    </div>
                </div>

                <div class="modal fade" id="uploadModal">
                    <div class="modal-dialog">
                        <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data"
                            class="modal-content">
                            @csrf

                            <div class="modal-header">
                                <h5 class="modal-title">Upload File</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="folder_id" id="uploadFolderId" value="">

                                <input type="file" name="file" class="form-control" required>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="createFolderModal">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('folders.store') }}" class="modal-content">
                            @csrf

                            <div class="modal-header">
                                <h5>Create Folder</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="text" name="name" class="form-control" placeholder="Folder name"
                                    required>

                                <input type="hidden" name="parent_id" id="createFolderParentId" value="">
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-primary">Create</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="renameModal">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('folders.mrename') }}" class="modal-content">
                            @csrf

                            <div class="modal-header">
                                <h5>Rename Folder</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="id" id="renameFolderId">
                                <input type="text" name="name" class="form-control" required>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-primary">Rename</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal fade" id="moveModal">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('folders.mmove') }}" class="modal-content">
                            @csrf

                            <div class="modal-header">
                                <h5>Move Folder</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <input type="hidden" name="id" id="moveFolderId">
                                <select name="parent_id" class="form-select">
                                    @foreach ($allfolders as $f)
                                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-primary">Move</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="shareModal">
                    <div class="modal-dialog">
                        <form method="POST" action="{{ route('folders.share') }}" class="modal-content">
                            @csrf

                            <div class="modal-header">
                                <h5 class="modal-title">Share Folder(s)</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">

                                <!-- Hidden selected IDs -->
                                <input type="hidden" name="folder_ids" id="shareFolderIds">

                                <!-- Emails -->
                                <label>Email(s)</label>
                                <input type="text" name="emails" class="form-control"
                                    placeholder="Enter emails (comma separated)" required>

                                <!-- Permission -->
                                <label class="mt-2">Permission</label>
                                <select name="permission" class="form-select">
                                    <option value="view">View Only</option>
                                    <option value="edit">Edit</option>
                                </select>

                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-primary">Share</button>
                            </div>
                        </form>
                    </div>
                </div>

        </div>






    @endsection

    @push('addOnCss')
        <style>
            .fb-breadcrumb {
                margin-bottom: 6px;
            }

            .fb-name-stack {
                display: flex;
                flex-direction: column;
                min-width: 0;
                gap: 1px;
            }

            .fb-item-path {
                margin: 0;
                font-size: 11px;
                color: #94a3b8;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 420px;
            }

            .fb-item-path-link {
                color: inherit;
                text-decoration: none;
            }

            .fb-item-path-link:hover {
                color: #2563eb;
                text-decoration: underline;
            }

            .fb-item-path-sep {
                margin: 0 4px;
                color: #cbd5e1;
            }

            .fb-page-title {
                margin: 0;
                font-size: 26px;
                font-weight: 700;
                color: #111827;
            }

            .fb-search-wrap {
                position: relative;
                min-width: 300px;
                margin: 0;
            }

            .fb-table tbody tr:hover {
                background: #f8fafc;
            }

            .fb-file-table tbody td:last-child {
                min-width: 70px;
            }

            .fb-files-title {
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: .5px;
                color: #64748b;
                margin-bottom: 10px;
                font-weight: 700;
            }

            .row-checkbox {
                margin-right: 8px;
            }

            .star-btn {
                font-size: 14px;
                cursor: pointer;
            }

            /* ── Bulk selection toolbar ── */
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

            .fb-tool-btn-danger {
                background: #fff !important;
                color: #dc2626 !important;
                border-color: #fecaca !important;
            }

            .fb-tool-btn-danger:hover {
                background: #fef2f2 !important;
                border-color: #dc2626 !important;
                color: #dc2626 !important;
            }

            .fb-col-check {
                width: 34px;
            }

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

            .fb-row-btn {
                padding: 5px 8px;
            }

            .fb-grid-name a {
                color: inherit;
                text-decoration: none;
            }

            #gridView .folder-card {
                border-radius: 10px;
                border: 1px solid #e5e7eb;
            }

            .fb-grid-card {
                height: 100%;
                position: relative;
            }

            #gridView .folder-card i.fa-folder {
                font-size: 46px !important;
            }

            #gridView .folder-card.selected {
                border-color: #60a5fa;
                background: #eff6ff;
            }

            .fb-tool-left,
            .fb-tool-right {
                flex-wrap: wrap;
            }

            /* ── Empty state ── */
            .fb-empty-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 4px;
                padding: 48px 24px;
                text-align: center;
                cursor: default;
            }

            /* Only the button itself should look clickable — cursor is
                               inherited, so it'd otherwise flow down to it too. */
            .fb-empty-cta {
                cursor: pointer;
            }

            .fb-empty-icon {
                width: 64px;
                height: 64px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(37, 52, 71, .07);
                color: #f59e0b;
                font-size: 26px;
                margin-bottom: 10px;
            }

            .fb-empty-title {
                font-size: 16px;
                font-weight: 700;
                color: #1e293b;
                margin: 0;
            }

            .fb-empty-sub {
                font-size: 13px;
                color: #94a3b8;
                margin: 4px 0 18px;
            }

            .fb-empty-cta {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 22px;
                border-radius: 10px;
                background: linear-gradient(135deg, #253447, #1a2737);
                color: #fff;
                font-size: 13px;
                font-weight: 700;
                text-decoration: none;
                box-shadow: 0 4px 14px rgba(37, 52, 71, .25);
                transition: transform .15s ease, box-shadow .15s ease;
            }

            .fb-empty-cta:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(37, 52, 71, .32);
                color: #fff;
            }
        </style>
    @endpush

    @push('script')
        <script>
            const rootFolder = @json($favoriteRootData);
            const sharedFoldersUrl = '{{ route('shared.folders') }}';
            let selectedIds = new Set();
            let activeSort = {
                key: 'name',
                direction: 'asc'
            };
            let query = '';
            const pathStack = [rootFolder];

            document.addEventListener('DOMContentLoaded', function() {

                const listView = document.getElementById('listView');
                const gridView = document.getElementById('gridView');
                const buttons = document.querySelectorAll('.view-btn');
                const selectAll = document.getElementById('selectAll');
                const searchInput = document.getElementById('fbSearchInput');

                function currentFolder() {
                    return pathStack[pathStack.length - 1];
                }

                function currentItems() {
                    return currentFolder().children || [];
                }

                function updateHiddenFolderIds() {
                    const currentId = currentFolder().id || '';
                    const uploadEl = document.getElementById('uploadFolderId');
                    const createEl = document.getElementById('createFolderParentId');

                    if (uploadEl) uploadEl.value = currentId;
                    if (createEl) createEl.value = currentId;
                }

                function updateBreadcrumb() {
                    const base = '<a href="#" class="fb-crumb">Favorites</a>';

                    const dynamic = pathStack.map((folder, index) => {
                        if (index === pathStack.length - 1) {
                            return `<span class="fb-crumb-sep">&gt;</span><span class="fb-crumb-current">${folder.name}</span>`;
                        }

                        return `<span class="fb-crumb-sep">&gt;</span><a href="#" class="fb-crumb fb-path-crumb" data-level="${index}">${folder.name}</a>`;
                    }).join('');

                    document.querySelector('.fb-breadcrumb').innerHTML = base + dynamic;
                    const pageTitle = document.getElementById('fbPageTitle');
                    if (pageTitle) {
                        pageTitle.textContent = currentFolder().name;
                    }
                }

                function filteredRows() {
                    return currentItems().filter((row) => row.name.toLowerCase().includes(query.toLowerCase()));
                }

                function sortedRows() {
                    const rows = filteredRows().slice();
                    rows.sort((a, b) => {
                        let av;
                        let bv;

                        if (activeSort.key === 'size') {
                            av = a.sizeValue || 0;
                            bv = b.sizeValue || 0;
                        } else if (activeSort.key === 'modified') {
                            av = a.modifiedTs || 0;
                            bv = b.modifiedTs || 0;
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
                    if (row.type === 'folder') {
                        return {
                            fa: 'fa-folder',
                            cls: 'fb-folder-icon'
                        };
                    }

                    const ext = ((row.ext || (row.name || '').split('.').pop()) || '').toLowerCase();

                    if (ext === 'pdf') {
                        return {
                            fa: 'fa-file-pdf',
                            cls: 'fb-file-pdf-icon'
                        };
                    }

                    if (['doc', 'docx'].includes(ext)) {
                        return {
                            fa: 'fa-file-word',
                            cls: 'fb-file-word-icon'
                        };
                    }

                    if (['xls', 'xlsx', 'csv'].includes(ext)) {
                        return {
                            fa: 'fa-file-excel',
                            cls: 'fb-file-excel-icon'
                        };
                    }

                    if (['ppt', 'pptx'].includes(ext)) {
                        return {
                            fa: 'fa-file-powerpoint',
                            cls: 'fb-file-ppt-icon'
                        };
                    }

                    if (['zip', 'rar', '7z'].includes(ext)) {
                        return {
                            fa: 'fa-file-zipper',
                            cls: 'fb-file-zip-icon'
                        };
                    }

                    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'].includes(ext)) {
                        return {
                            fa: 'fa-file-image',
                            cls: 'fb-file-img-icon'
                        };
                    }

                    return {
                        fa: 'fa-file',
                        cls: 'fb-file-icon'
                    };
                }

                // Clickable ancestor path (e.g. "Project Palm / 4 - QofE /
                // Databooks") — a favorited item can be buried several
                // folders deep with no favorited parent shown anywhere else
                // in this tree, so this is the only place that reveals
                // where it actually lives. Each segment jumps straight to
                // that folder on the Drive page.
                function buildPathHtml(row) {
                    const crumbs = row.breadcrumb || [];
                    if (!crumbs.length) return '';

                    const parts = crumbs.map((folder, i) => {
                        const ids = crumbs.slice(0, i + 1).map(f => f.id).join(',');
                        return `<a href="${sharedFoldersUrl}#path=${ids}" class="fb-item-path-link" target="_blank" rel="noopener">${escapeHtml(folder.name)}</a>`;
                    });

                    return `<p class="fb-item-path">${parts.join('<span class="fb-item-path-sep">/</span>')}</p>`;
                }

                function escapeHtml(str) {
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                }

                /* Distinguishes "you have zero favorites" from "your search
                   matched none of your favorites" — showing the same "Add
                   Favorite" empty state for a no-results search was
                   misleading, since favorites already exist, the search
                   term just doesn't match any of them. */
                function emptyStateHtml() {
                    if (query) {
                        return `
                            <div class="fb-empty-state">
                                <div class="fb-empty-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                                <p class="fb-empty-title">No results found</p>
                                <p class="fb-empty-sub">No favorites match &quot;${escapeHtml(query)}&quot;.</p>
                            </div>`;
                    }

                    return `
                        <div class="fb-empty-state">
                            <div class="fb-empty-icon"><i class="fa-solid fa-star"></i></div>
                            <p class="fb-empty-title">No favorites yet</p>
                            <p class="fb-empty-sub">Star files or folders in Project Folders to find them here quickly.</p>
                            <a href="{{ route('shared.folders') }}" class="fb-empty-cta">
                                <i class="fa-solid fa-star"></i> Add Favorite
                            </a>
                        </div>`;
                }

                function renderList() {
                    const rows = sortedRows();
                    const atRoot = pathStack.length === 1;

                    const html = rows.map((row) => {
                        const starClass = row.favorite ? 'active fa-solid' : 'fa-regular';
                        const {
                            fa: icon,
                            cls: iconClass
                        } = getItemIcon(row);
                        const isSelected = atRoot && selectedIds.has(String(row.id));
                        const rowClass = (row.type === 'folder' ? 'fb-item-folder' : '') + (isSelected ?
                            ' fb-row-selected' : '');
                        const openId = row.type === 'folder' ? `data-open-id="${row.id}"` : '';
                        const showView = row.type === 'file' && !!row.previewUrl;
                        const iconHtml = showView ?
                            `<a href="${row.previewUrl}" class="fb-name-link"><i class="fa-solid ${icon} ${iconClass}"></i></a>` :
                            `<i class="fa-solid ${icon} ${iconClass}"></i>`;
                        const nameHtml = showView ?
                            `<a href="${row.previewUrl}" class="fb-name-link">${row.name}</a>` :
                            `<span>${row.name}</span>`;
                        const pathHtml = buildPathHtml(row);
                        const actionCell = `<div class="fb-row-actions">
                                <i class="fa-regular fa-star star-btn ${starClass}" data-id="${row.id}" title="Favorite"></i>
                               </div>`;
                        const checkCell = atRoot ?
                            `<input type="checkbox" class="fb-row-check" data-id="${row.id}" ${isSelected ? 'checked' : ''}>` :
                            '';

                        return `
                        <tr class="${rowClass}" ${openId}>
                            <td>${checkCell}</td>
                            <td>
                                <div class="fb-name-cell">
                                    ${iconHtml}
                                    <div class="fb-name-stack">
                                        ${nameHtml}
                                        ${pathHtml}
                                    </div>
                                </div>
                            </td>
                            <td>${row.size || '-'}</td>
                            <td>${row.modified || '-'}</td>
                            <td>${row.creator || '-'}</td>
                            <td>${actionCell}</td>
                        </tr>`;
                    }).join('');

                    document.getElementById('fbListBody').innerHTML = html ||
                        `<tr><td colspan="6">${emptyStateHtml()}</td></tr>`;
                }

                function renderGrid() {
                    const rows = sortedRows();
                    const atRoot = pathStack.length === 1;

                    const html = rows.map((row) => {
                        const starClass = row.favorite ? 'active fa-solid' : 'fa-regular';
                        const {
                            fa: icon,
                            cls: iconClass
                        } = getItemIcon(row);
                        const isSelected = atRoot && selectedIds.has(String(row.id));
                        const clickable = (row.type === 'folder' ? 'fb-item-folder' : '') + (isSelected ?
                            ' fb-row-selected' : '');
                        const openId = row.type === 'folder' ? `data-open-id="${row.id}"` : '';
                        const showView = row.type === 'file' && !!row.previewUrl;
                        const iconHtml = showView ?
                            `<a href="${row.previewUrl}" class="fb-name-link"><i class="fa-solid ${icon} ${iconClass}"></i></a>` :
                            `<i class="fa-solid ${icon} ${iconClass}"></i>`;
                        const nameHtml = showView ?
                            `<a href="${row.previewUrl}" class="fb-name-link">${row.name}</a>` :
                            row.name;
                        const pathHtml = buildPathHtml(row);
                        const actions = `<div class="mt-2 d-flex justify-content-center gap-2">
                                <i class="fa-regular fa-star star-btn ${starClass}" data-id="${row.id}" title="Favorite"></i>
                            </div>`;
                        const checkWrap = atRoot ?
                            `<label class="fb-grid-check-wrap" style="position:absolute; top:8px; left:8px; z-index:2;">
                                    <input type="checkbox" class="fb-row-check" data-id="${row.id}" ${isSelected ? 'checked' : ''}>
                                </label>` :
                            '';

                        return `
                        <div class="col-md-3">
                            <div class="card folder-card fb-grid-card p-3 selectable-card ${clickable}" data-id="${row.id}" ${openId}>
                                ${checkWrap}
                                <div class="fb-grid-icon text-center">
                                    ${iconHtml}
                                </div>

                                <h6 class="fb-grid-name text-truncate">${nameHtml}</h6>
                                ${pathHtml ? `<div class="text-center">${pathHtml}</div>` : ''}
                                <small class="fb-grid-meta d-block">${row.size || '-'} • ${row.creator || '-'}</small>
                                ${actions}
                            </div>
                        </div>`;
                    }).join('');

                    document.getElementById('fbGridBody').innerHTML = html ||
                        `<div class="col-12">${emptyStateHtml()}</div>`;
                }

                function renderAll() {
                    renderList();
                    renderGrid();
                    updateHiddenFolderIds();
                    updateSelectedCount();
                }

                function updateSelectedCount() {
                    const atRoot = pathStack.length === 1;
                    const bulkRow = document.querySelector('.fb-bulk-row');
                    if (bulkRow) {
                        bulkRow.classList.toggle('d-none', !atRoot);
                    }

                    const countEl = document.getElementById('fbSelectedCount');
                    if (countEl) {
                        countEl.textContent = selectedIds.size + ' selected';
                    }

                    if (selectAll) {
                        const visibleIds = atRoot ? sortedRows().map((r) => String(r.id)) : [];
                        const visibleSelected = visibleIds.filter((id) => selectedIds.has(id)).length;
                        selectAll.checked = visibleIds.length > 0 && visibleSelected === visibleIds.length;
                        selectAll.indeterminate = visibleSelected > 0 && visibleSelected < visibleIds.length;
                    }
                }

                function openFolder(id) {
                    const target = currentItems().find((item) => String(item.id) === String(id) && item.type ===
                        'folder');
                    if (!target) return;

                    pathStack.push(target);
                    selectedIds.clear();
                    query = '';
                    searchInput.value = '';
                    updateBreadcrumb();
                    renderAll();
                }

                /* =========================
                   VIEW TOGGLE
                ========================== */

                const setView = (view) => {

                    // FOLDER VIEW
                    listView.classList.toggle('d-none', view === 'grid');
                    gridView.classList.toggle('d-none', view !== 'grid');

                    buttons.forEach(btn => {
                        btn.classList.toggle('active', btn.dataset.view === view);
                    });

                    localStorage.setItem('viewMode', view);
                };





                setView(localStorage.getItem('viewMode') || 'list');

                buttons.forEach(btn => {
                    btn.addEventListener('click', () => setView(btn.dataset.view));
                });

                searchInput.addEventListener('input', function() {
                    query = this.value.trim();
                    renderAll();
                });

                document.addEventListener('click', function(e) {
                    const crumb = e.target.closest('.fb-path-crumb');
                    if (!crumb) return;

                    e.preventDefault();
                    const level = Number(crumb.dataset.level);
                    pathStack.splice(level + 1);
                    selectedIds.clear();
                    query = '';
                    searchInput.value = '';
                    updateBreadcrumb();
                    renderAll();
                });

                document.addEventListener('click', function(e) {
                    const row = e.target.closest('[data-open-id]');
                    if (!row) return;

                    if (e.target.closest('button, input, .star-btn, a')) {
                        return;
                    }

                    openFolder(row.dataset.openId);
                });

                /* =========================
                   FAVORITE
                ========================== */
                document.addEventListener('click', function(e) {

                    if (!e.target.classList.contains('star-btn')) return;

                    const star = e.target;
                    const id = star.dataset.id;
                    const isActive = star.classList.contains('active');

                    star.classList.toggle('active');
                    star.classList.toggle('fa-solid');
                    star.classList.toggle('fa-regular');

                    fetch('/toggle-favorite', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            id: id,
                            favorite: !isActive
                        })
                    }).then(() => {
                        const idStr = String(id);

                        // If item was de-selected as favorite on Favorite page, remove it from current folder view.
                        if (isActive) {
                            const folder = currentFolder();
                            folder.children = (folder.children || []).filter((item) => String(item
                                .id) !== idStr);
                            selectedIds.delete(idStr);
                            if (selectAll) {
                                selectAll.checked = false;
                            }
                            renderAll();
                        }

                        showToast('Saved successfully', 'success');
                    }).catch(() => {
                        // rollback
                        star.classList.toggle('active');
                        star.classList.toggle('fa-solid');
                        star.classList.toggle('fa-regular');
                    });

                });

                /* =========================
                   BULK SELECTION
                ========================== */
                if (selectAll) {
                    selectAll.addEventListener('change', function() {
                        const checked = this.checked;
                        sortedRows().forEach((row) => {
                            if (checked) {
                                selectedIds.add(String(row.id));
                            } else {
                                selectedIds.delete(String(row.id));
                            }
                        });
                        renderAll();
                    });
                }

                document.addEventListener('change', function(e) {
                    if (!e.target.classList.contains('fb-row-check')) return;

                    const id = String(e.target.dataset.id);
                    if (e.target.checked) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                    e.target.closest('tr, .fb-grid-card')?.classList.toggle('fb-row-selected', e.target
                        .checked);
                    updateSelectedCount();
                });

                function unfavoriteByIds(ids) {
                    return Promise.all(ids.map((id) => fetch('/toggle-favorite', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            id: id,
                            favorite: false
                        })
                    })));
                }

                const unfavoriteSelectedBtn = document.getElementById('unfavoriteSelectedBtn');
                if (unfavoriteSelectedBtn) {
                    unfavoriteSelectedBtn.addEventListener('click', function() {
                        const favoritedSelectedIds = currentItems()
                            .filter((item) => item.favorite && selectedIds.has(String(item.id)))
                            .map((item) => String(item.id));

                        if (favoritedSelectedIds.length === 0) {
                            showToast('Select favorited items to unfavorite first', 'danger');
                            return;
                        }

                        Swal.fire({
                            title: 'Unfavorite selected?',
                            html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-star-half-stroke"></i></div>' +
                                favoritedSelectedIds.length +
                                ' item(s) will be removed from your favorites.',
                            width: '380px',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            confirmButtonText: 'Yes, unfavorite',
                            cancelButtonText: 'Cancel',
                            customClass: {
                                popup: 'swal-theme'
                            },
                            reverseButtons: true,
                        }).then((result) => {
                            if (!result.isConfirmed) return;

                            unfavoriteByIds(favoritedSelectedIds).then(() => {
                                const idSet = new Set(favoritedSelectedIds);
                                const folder = currentFolder();
                                folder.children = (folder.children || []).filter((item) => !
                                    idSet.has(String(item.id)));
                                rootFolder.children = (rootFolder.children || []).filter((
                                    item) => !idSet.has(String(item.id)));
                                favoritedSelectedIds.forEach((id) => selectedIds.delete(id));
                                renderAll();
                                showToast('Removed from favorites', 'success');
                            }).catch(() => {
                                showToast('Failed to unfavorite selected items', 'danger');
                            });
                        });
                    });
                }

                const removeAllFavoritesBtn = document.getElementById('removeAllFavoritesBtn');
                if (removeAllFavoritesBtn) {
                    removeAllFavoritesBtn.addEventListener('click', function() {
                        const allIds = (rootFolder.children || []).map((item) => String(item.id));

                        if (allIds.length === 0) {
                            showToast('No favorites to remove', 'danger');
                            return;
                        }

                        Swal.fire({
                            title: 'Remove all favorites?',
                            html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash-can"></i></div>' +
                                'All ' + allIds.length +
                                ' favorited item(s) will be removed from your favorites.',
                            width: '380px',
                            showCancelButton: true,
                            confirmButtonColor: '#dc2626',
                            confirmButtonText: 'Yes, remove all',
                            cancelButtonText: 'Cancel',
                            customClass: {
                                popup: 'swal-theme'
                            },
                            reverseButtons: true,
                        }).then((result) => {
                            if (!result.isConfirmed) return;

                            unfavoriteByIds(allIds).then(() => {
                                pathStack.splice(1);
                                rootFolder.children = [];
                                selectedIds.clear();
                                query = '';
                                searchInput.value = '';
                                updateBreadcrumb();
                                renderAll();
                                showToast('All favorites removed', 'success');
                            }).catch(() => {
                                showToast('Failed to remove all favorites', 'danger');
                            });
                        });
                    });
                }

                document.addEventListener('click', function(e) {
                    const actionBtn = e.target.closest('[data-action]');
                    if (!actionBtn) return;

                    const action = actionBtn.dataset.action;
                    const id = actionBtn.dataset.id;

                    if (action === 'copy') {
                        selectedIds = new Set([String(id)]);
                        handleCopy();
                    }
                });

                updateBreadcrumb();
                renderAll();

            });

            /* =========================
               SHARE
            ========================== */
            function openShareModal() {

                if (selectedIds.size === 0) {
                    alert('Select folders to share');
                    return;
                }

                document.getElementById('shareFolderIds').value = [...selectedIds].join(',');

                let modal = new bootstrap.Modal(document.getElementById('shareModal'));
                modal.show();
            }

            function getSelectedSingleId() {
                if (selectedIds.size === 0) {
                    alert('Please select a folder');
                    return null;
                }

                if (selectedIds.size > 1) {
                    alert('Only one folder allowed for this action');
                    return null;
                }

                return [...selectedIds][0];
            }

            function handleRename() {

                let id = getSelectedSingleId();
                if (!id) return;

                document.getElementById('renameFolderId').value = id;

                new bootstrap.Modal(document.getElementById('renameModal')).show();
            }

            function handleMove() {

                let id = getSelectedSingleId();
                if (!id) return;

                document.getElementById('moveFolderId').value = id;

                new bootstrap.Modal(document.getElementById('moveModal')).show();
            }

            function handleCopy() {

                if (selectedIds.size === 0) {
                    alert('Select folders first');
                    return;
                }

                fetch("{{ route('folders.mcopyMultiple') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            ids: [...selectedIds]
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        alert(data.message || 'Copied successfully');
                        location.reload();
                    });
            }
        </script>
    @endpush
