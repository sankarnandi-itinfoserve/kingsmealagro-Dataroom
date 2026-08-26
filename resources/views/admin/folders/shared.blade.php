@extends('admin.layouts.app')
@section('title', 'Project Folders Dashboard')
@section('page_title', 'Project Folders Dashboard')

@section('content')


    <div class="container-fluid mt-3 shared-folders">

        <!-- Breadcrumb -->

        <div class="mb-2 text-muted small">
            <a href="{{ route('shared.folders') }}">Project Folders</a>

            @foreach ($breadcrumb as $crumb)
                > <a href="{{ route('shared.folders', ['parent_id' => $crumb->id]) }}">
                    {{ $crumb->name }}
                </a>
            @endforeach
        </div>



        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-3">

            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-folder text-warning fs-4"></i>
                <h5 class="mb-0">
                    {{ $currentFolder->name ?? 'Shared Folder – Virtual Data Room' }}
                </h5>
            </div>

            <div class="dropdown">
                <button class="btn btn-light btn-sm d-flex align-items-center gap-1" type="button" id="moreOptionsDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">

                    <i class="fa-solid fa-ellipsis"></i>
                    <span class="text-muted">More Options</span>
                </button>

                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="moreOptionsDropdown">


                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#uploadModal">
                            <i class="fa-solid fa-upload me-2"></i> Upload File
                        </a>
                    </li>

                    <li>
                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#createFolderModal">
                            <i class="fa-solid fa-folder-plus me-2"></i> New Folder
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#" onclick="openShareModal()">
                            <i class="fa-solid fa-share-nodes me-2"></i> Share
                        </a>
                    </li>
                    {{-- <li>
                    <a class="dropdown-item text-danger" href="#">
                        <i class="fa-solid fa-trash me-2"></i> Delete
                    </a>
                </li> --}}
                </ul>
            </div>
        </div>


        <!-- Table -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div></div>
                    <div class="view-toggle d-flex gap-2">
                        <i class="fa-solid fa-bars view-btn active" data-view="list"></i>
                        <i class="fa-solid fa-table-cells-large view-btn" data-view="grid"></i>
                    </div>

                </div>
                <div id="listView">

                    <table class="table align-middle mb-0">
                        <!-- Table Head -->
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px;">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th>Name</th>
                                <th style="width:100px;">Size</th>
                                <th style="width:160px;">Last modified</th>
                                <th style="width:140px;">Creator</th>
                            </tr>
                        </thead>
                        <!-- Table Body -->
                        <tbody>
                            @foreach ($folders as $folder)
                                <tr>
                                    <td>
                                        <div class="d-flex">
                                            <input type="checkbox" class="row-checkbox" value="{{ $folder->id }}">

                                            <i class="fa-regular fa-star star-btn {{ $folder->favorites->where('user_id', auth()->id())->count() ? 'active fa-solid' : '' }}"
                                                data-id="{{ $folder->id }}">
                                            </i>
                                        </div>
                                    </td>

                                    <td>
                                        <i class="fa-solid fa-folder text-warning me-2"></i>
                                        <a href="{{ route('shared.folders', ['parent_id' => $folder->id]) }}">
                                            {{ $folder->name }}
                                        </a>
                                    </td>

                                    <td>{{ number_format($folder->getTotalSize() / 1024 / 1024, 2) }} MB</td>

                                    <td>{{ $folder->updated_at->format('d M Y h:i A') }}</td>

                                    <td>{{ ($folder->creator->fname ?? '') . ' ' . ($folder->creator->lname ?? '') ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                    <h6 class="mt-4">Files</h6>
                    <!-- FILE LIST VIEW -->
                    <div id="fileListView">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($files ?? [] as $file)
                                    <tr>
                                        <td>
                                            <i class="fa-solid fa-file me-2 text-secondary"></i>
                                            {{ $file->name }}
                                        </td>
                                        <td>{{ strtoupper($file->file_type) }}</td>
                                        <td>{{ number_format($file->size / 1024, 2) }} KB</td>
                                        <td class="d-flex gap-2">
                                            <a href="{{ route('files.preview', base64_encode($file->id)) }}"
                                                class="text-info" title="Preview">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <a href="{{ route('files.download', $file->id) }}" class="text-success"
                                                title="Download">
                                                <i class="fa-solid fa-download"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
                <!-- GRID VIEW -->
                <div id="gridView" class="d-none">
                    <div class="row g-3">

                        @foreach ($folders as $folder)
                            <div class="col-md-3">
                                <div class="card folder-card p-3 selectable-card" data-id="{{ $folder->id }}">

                                    <div class="d-flex justify-content-between">
                                        <input type="checkbox" class="grid-checkbox" value="{{ $folder->id }}">

                                        <i class="fa-regular fa-star star-btn {{ $folder->favorites->where('user_id', auth()->id())->count() ? 'active fa-solid' : '' }}"
                                            data-id="{{ $folder->id }}">
                                        </i>
                                    </div>

                                    <div class="mt-2">
                                        <i class="fa-solid fa-folder text-warning fs-3"></i>
                                    </div>

                                    <h6 class="mt-2">
                                        <a href="{{ route('shared.folders', ['parent_id' => $folder->id]) }}">
                                            {{ $folder->name }}
                                        </a>
                                    </h6>

                                    <small class="text-muted">
                                        {{ number_format($folder->getTotalSize() / 1024 / 1024, 2) }} MB
                                    </small>

                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>
                <!-- FILE GRID VIEW -->
                <div id="fileGridView" class="d-none">
                    <div class="row g-3">

                        @foreach ($files ?? [] as $file)
                            <div class="col-md-3">
                                <div class="card p-3 text-center">

                                    <i class="fa-solid fa-file fs-2 text-secondary"></i>

                                    <h6 class="mt-2 text-truncate">
                                        {{ $file->name }}
                                    </h6>

                                    <small class="text-muted">
                                        {{ strtoupper($file->file_type) }} • {{ number_format($file->size / 1024, 2) }} KB
                                    </small>

                                    <div class="mt-2 d-flex justify-content-center gap-3">
                                        <a href="{{ route('files.preview', base64_encode($file->id)) }}" class="text-info"
                                            title="Preview">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <a href="{{ route('files.download', $file->id) }}" class="text-success"
                                            title="Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>

                                </div>
                            </div>
                        @endforeach

                    </div>
                </div>

            </div>
        </div>

        <!-- Footer Option -->
        <div class="text-end mt-2 small text-muted">
            Email me when a file is
            <span class="text-primary fw-semibold">Uploaded</span> to this folder
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
                        <input type="hidden" name="folder_id" value="{{ $currentFolder->id ?? '' }}">

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
                        <input type="text" name="name" class="form-control" placeholder="Folder name" required>

                        <input type="hidden" name="parent_id" value="{{ $currentFolder->id ?? '' }}">
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

@push('script')
    <script>
        let selectedIds = new Set();
        let isRootLevel = @json(is_null($currentFolder));

        document.addEventListener('DOMContentLoaded', function() {

            const listView = document.getElementById('listView');
            const gridView = document.getElementById('gridView');
            const buttons = document.querySelectorAll('.view-btn');
            const selectAll = document.getElementById('selectAll');
            const fileListView = document.getElementById('fileListView');
            const fileGridView = document.getElementById('fileGridView');

            /* =========================
               VIEW TOGGLE
            ========================== */

            const setView = (view) => {

                // FOLDER VIEW
                listView.classList.toggle('d-none', view === 'grid');
                gridView.classList.toggle('d-none', view !== 'grid');

                // FILE VIEW
                if (fileListView && fileGridView) {
                    fileListView.classList.toggle('d-none', view === 'grid');
                    fileGridView.classList.toggle('d-none', view !== 'grid');
                }

                buttons.forEach(btn => {
                    btn.classList.toggle('active', btn.dataset.view === view);
                });

                localStorage.setItem('viewMode', view);
            };


            setView(localStorage.getItem('viewMode') || 'list');

            buttons.forEach(btn => {
                btn.addEventListener('click', () => setView(btn.dataset.view));
            });

            /* =========================
               HANDLE SELECTION (COMMON)
            ========================== */
            function handleSelection(id, checked) {

                if (isRootLevel) {
                    // Only ONE allowed
                    selectedIds.clear();

                    document.querySelectorAll('.row-checkbox, .grid-checkbox').forEach(cb => {
                        cb.checked = false;
                        cb.closest('tr, .folder-card')?.classList.remove('selected');
                    });

                    if (checked) {
                        selectedIds.add(id);
                        syncSelectionUI(id, true);
                    }

                } else {
                    // MULTI
                    if (checked) {
                        selectedIds.add(id);
                    } else {
                        selectedIds.delete(id);
                    }
                    syncSelectionUI(id, checked);
                }
            }

            /* =========================
               SYNC UI (LIST + GRID)
            ========================== */
            function syncSelectionUI(id, checked) {

                // LIST
                document.querySelectorAll(`.row-checkbox[value="${id}"]`).forEach(cb => {
                    cb.checked = checked;
                    cb.closest('tr')?.classList.toggle('selected', checked);
                });

                // GRID
                document.querySelectorAll(`.grid-checkbox[value="${id}"]`).forEach(cb => {
                    cb.checked = checked;
                    cb.closest('.folder-card')?.classList.toggle('selected', checked);
                });
            }

            /* =========================
               LIST CHECKBOX
            ========================== */
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('row-checkbox')) {
                    handleSelection(e.target.value, e.target.checked);
                }
            });

            /* =========================
               GRID CHECKBOX
            ========================== */
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('grid-checkbox')) {
                    handleSelection(e.target.value, e.target.checked);
                }
            });

            /* =========================
               SELECT ALL (ONLY CHILD LEVEL)
            ========================== */
            if (selectAll && !isRootLevel) {
                selectAll.addEventListener('change', function() {

                    document.querySelectorAll('.row-checkbox').forEach(cb => {
                        handleSelection(cb.value, this.checked);
                    });

                });
            } else if (selectAll) {
                // disable at root
                selectAll.disabled = true;
            }

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
                }).catch(() => {
                    // rollback
                    star.classList.toggle('active');
                    star.classList.toggle('fa-solid');
                    star.classList.toggle('fa-regular');
                });

            });

        });

        /* =========================
           DOWNLOAD
        ========================== */
        function bulkDownload() {

            let ids = [];

            // ✅ If user selected folders
            if (selectedIds.size > 0) {
                ids = [...selectedIds];
            } else {

                // ✅ Extract folder ID from URL
                let pathParts = window.location.pathname.split('/');

                // Example: /folders/shared/3
                let currentFolderId = pathParts[pathParts.length - 1];

                // Validate it's a number
                if (!isNaN(currentFolderId) && currentFolderId !== '') {
                    ids.push(currentFolderId);
                } else {
                    alert('Select folders first');
                    return;
                }
            }

            // ✅ Redirect
            window.location.href =
                '/folders/download-multiples?ids=' + ids.join(',');
        }

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

            let ids = [...selectedIds];

            // ✅ If nothing selected → fallback to current folder from URL
            if (ids.length === 0) {

                let pathParts = window.location.pathname.split('/');
                let folderId = pathParts[pathParts.length - 1];

                if (folderId && !isNaN(folderId)) {
                    ids.push(folderId);
                } else {
                    alert('Select folders first');
                    return;
                }
            }

            fetch("{{ route('folders.mcopyMultiple') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        ids: ids
                    })
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || 'Copied successfully');
                    location.reload();
                })
                .catch(() => {
                    alert('Copy failed');
                });
        }
    </script>
@endpush
