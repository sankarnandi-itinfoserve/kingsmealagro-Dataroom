@extends('admin.layouts.app')

@section('title', 'Edit — ' . $project->name)
@section('page_title', 'Edit Project')

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3 mb-3 d-flex align-items-start gap-2" style="font-size:13.5px;">
            <i class="fa-solid fa-circle-exclamation mt-1 flex-shrink-0"></i>
            <div>
                <strong>Please fix the following:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="mb-3 d-flex align-items-center gap-3">
        <button type="button" class="prj-back-btn" onclick="history.back()">
            <i class="fa-solid fa-arrow-left"></i> Back
        </button>
        <span class="prj-back-title">{{ $project->name }}</span>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <form action="{{ route('projects.update', $project) }}" method="POST" id="projectEditForm">
                @csrf @method('PUT')

                <div class="prj-form-card">
                    <div class="prj-form-section-header">
                        <div class="prj-section-title">Project Details</div>
                    </div>
                    <div class="prj-form-section-body">

                        <div class="row g-3">
                            <div class="col-12">
                                <label class="prj-label">
                                    Project Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name"
                                    class="prj-input @error('name') is-invalid @enderror"
                                    value="{{ old('name', $project->name) }}" required autocomplete="off">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="prj-form-actions mt-4">
                            <button type="submit" class="prj-submit-btn" id="submitBtn">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Save Changes
                            </button>
                            <a href="{{ route('projects.index') }}" class="prj-cancel-btn">
                                Cancel
                            </a>
                            {{-- Delete trigger (button only — form is outside the edit form below) --}}
                            <button type="button" class="prj-danger-btn" id="deleteProjectBtn">
                                <i class="fa-solid fa-trash me-1"></i> Delete Project
                            </button>
                        </div>

                    </div>
                </div>

            </form>

            {{-- Delete form lives OUTSIDE the edit form to prevent nested-form collision --}}
            <form action="{{ route('projects.destroy', $project) }}" method="POST" id="deleteProjectForm">
                @csrf @method('DELETE')
            </form>
        </div>

        {{-- ── Project files panel ── --}}
        <div class="col-lg-6">
            <div class="prj-form-card">
                <div class="prj-form-section-header d-flex align-items-center justify-content-between">
                    <div class="prj-section-title">Project Folders & Files</div>
                    <div class="d-flex gap-2">
                        <button type="button" class="pft-tool-btn" id="pftExpandCollapseBtn" data-expanded="false">
                            <i class="fa-solid fa-angle-double-down"></i> Expand All
                        </button>
                        <button type="button" class="pft-tool-btn" id="pftAddFolderBtn">
                            <i class="fa-solid fa-folder-plus"></i> Add Folder
                        </button>
                        <button type="button" class="pft-tool-btn" id="pftAddFileBtn">
                            <i class="fa-solid fa-file-circle-plus"></i> Add File
                        </button>
                    </div>
                </div>
                <div class="prj-form-section-body">
                    <div class="pft-tree" id="pftTree">
                        @if ($project->childrenRecursive->isEmpty())
                            <p class="pft-empty">No sub-folders or files yet.</p>
                        @else
                            @include('admin.projects._file_tree', [
                                'nodes' => $project->childrenRecursive,
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Add Folder modal ── --}}
    <div id="pftAddFolderModal" class="pft-modal-overlay d-none">
        <div class="pft-modal">
            <div class="pft-modal-header">
                <span>New Folder</span>
                <button type="button" class="pft-modal-close" data-modal-close="pftAddFolderModal">&times;</button>
            </div>
            <div class="pft-modal-body">
                <p class="pft-modal-target">Inside "<span id="pftAddFolderTarget"></span>"</p>
                <label class="prj-label">Folder Name</label>
                <input type="text" id="pftNewFolderName" class="prj-input" placeholder="e.g. Financial Documents">
                <div class="pft-modal-error" id="pftAddFolderError"></div>
            </div>
            <div class="pft-modal-footer">
                <button type="button" class="prj-cancel-btn" data-modal-close="pftAddFolderModal">Cancel</button>
                <button type="button" class="prj-submit-btn" id="pftAddFolderConfirm">Create Folder</button>
            </div>
        </div>
    </div>

    {{-- ── Add File modal ── --}}
    <div id="pftAddFileModal" class="pft-modal-overlay d-none">
        <div class="pft-modal">
            <div class="pft-modal-header">
                <span>Upload File</span>
                <button type="button" class="pft-modal-close" data-modal-close="pftAddFileModal">&times;</button>
            </div>
            <div class="pft-modal-body">
                <p class="pft-modal-target">Inside "<span id="pftAddFileTarget"></span>"</p>
                <label class="prj-label">Choose File</label>
                <label class="pft-file-drop" for="pftNewFileInput" id="pftFileDropZone">
                    <input type="file" id="pftNewFileInput" class="pft-file-input-native" multiple>
                    <span class="pft-file-drop-icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                    <span class="pft-file-drop-text">
                        <span class="pft-file-drop-title">Click to browse or drag files here</span>
                        <span class="pft-file-drop-sub" id="pftFileDropSub">No file chosen</span>
                    </span>
                </label>
                <div class="pft-modal-error" id="pftAddFileError"></div>
            </div>
            <div class="pft-modal-footer">
                <button type="button" class="prj-cancel-btn" data-modal-close="pftAddFileModal">Cancel</button>
                <button type="button" class="prj-submit-btn" id="pftAddFileConfirm">Upload</button>
            </div>
        </div>
    </div>

@endsection

@push('addOnCss')
    <style>
        .prj-form-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            margin-bottom: 16px;
            overflow: hidden;
            border: 1px solid #f1f5f9;
        }

        .prj-form-section-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 22px;
            background: #2D3E50;
            border-bottom: none;
        }

        .prj-section-title {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
        }

        .prj-form-section-body {
            padding: 22px;
        }

        .prj-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .prj-input {
            width: 100%;
            height: 40px;
            padding: 0 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 9px;
            font-size: 13.5px;
            color: #1e293b;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
        }

        .prj-input:focus {
            border-color: #2D3E50;
            box-shadow: 0 0 0 3px rgba(45, 62, 80, .09);
        }

        .prj-input.is-invalid { border-color: #dc2626; }

        select.prj-input { cursor: pointer; }

        .prj-form-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .prj-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 28px;
            border-radius: 10px;
            background: #2D3E50;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background .13s, transform .1s;
            box-shadow: 0 4px 14px rgba(45, 62, 80, .22);
        }

        .prj-submit-btn:hover { background: #1e2d3d; transform: translateY(-1px); }
        .prj-submit-btn:active { transform: translateY(0); }

        .prj-cancel-btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            border-radius: 10px;
            border: 1.5px solid #e2e8f0;
            background: transparent;
            color: #64748b;
            font-size: 13.5px;
            font-weight: 500;
            text-decoration: none;
            transition: background .12s, border-color .12s, color .12s;
        }

        .prj-cancel-btn:hover { background: #f1f5f9; border-color: #cbd5e1; color: #374151; }

        .prj-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            height: 30px;
            padding: 0 13px;
            border: 1px solid #dbe4f0;
            border-radius: 7px;
            background: #fff;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: border-color .14s, background .14s, color .14s;
        }

        .prj-back-btn:hover { border-color: #cbd5e1; background: #f8fafc; color: #253447; }

        .prj-back-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
        }

        .prj-danger-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 9px;
            border: 1.5px solid #fca5a5;
            background: #fff5f5;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background .13s, border-color .13s, color .13s;
        }

        .prj-danger-btn:hover { background: #dc2626; border-color: #dc2626; color: #fff; }

        /* ── Project Files panel ───────────────────────────────────────────── */
        .pft-tool-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 7px;
            border: 1px solid rgba(255,255,255,.25);
            background: rgba(255,255,255,.08);
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background .13s;
        }

        .pft-tool-btn:hover { background: rgba(255,255,255,.18); }

        .pft-tree {
            font-size: 13px;
            max-height: 480px;
            overflow-y: auto;
        }

        .pft-empty {
            font-size: 12.5px;
            color: #94a3b8;
            margin: 0;
            padding: 8px 2px;
        }

        .pft-node { margin: 0; }

        .pft-item {
            display: flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 7px;
            min-height: 25px;
            padding: 6px 4px;
            border-radius: 6px;
        }

        .pft-item:hover { background: #f8fafc; }

        .pft-item-active { background: #eff6ff; }

        .pft-toggle {
            box-sizing: border-box;
            width: 16px;
            height: 16px;
            border: none;
            background: none;
            padding: 0;
            margin: 0;
            line-height: 1;
            color: #94a3b8;
            font-size: 10px;
            cursor: pointer;
            flex-shrink: 0;
            transition: transform .13s;
        }

        .pft-node.open > .pft-item .pft-toggle {
            transform: rotate(90deg);
        }

        .pft-spacer { width: 16px; height: 16px; flex-shrink: 0; }

        .pft-folder-icon { color: #f59e0b; font-size: 13px; flex-shrink: 0; }
        .pft-file-icon { color: #94a3b8; font-size: 13px; flex-shrink: 0; }

        .pft-name {
            color: #334155;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            flex: 1 1 auto;
            min-width: 0;
        }

        .pft-name-link {
            text-decoration: underline;
            text-decoration-color: transparent;
            transition: text-decoration-color .15s;
        }

        .pft-name-link:hover {
            color: #253447;
            text-decoration-color: currentColor;
        }

        .pft-size {
            color: #94a3b8;
            font-size: 11.5px;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .pft-template-note {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12.5px;
            color: #0f5b52;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6fbf6 100%);
            border: 1px solid #b6f0e2;
            border-radius: 10px;
            padding: 10px 14px;
            margin-bottom: 12px;
            box-shadow: 0 1px 2px rgba(15, 91, 82, .06);
        }

        .pft-template-note-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            border-radius: 8px;
            background: #ffffff;
            color: #0f766e;
            font-size: 13px;
            box-shadow: 0 1px 3px rgba(15, 91, 82, .15);
        }

        .pft-template-note-text {
            line-height: 1.4;
        }

        .pft-template-note-pill {
            display: inline-flex;
            align-items: center;
            padding: 1px 9px;
            margin: 0 2px;
            border-radius: 20px;
            background: #0f766e;
            color: #fff;
            font-weight: 700;
            font-size: 11.5px;
            letter-spacing: .01em;
            vertical-align: middle;
        }

        .pft-template-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-left: 8px;
            padding: 1px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .02em;
            background: #0d9488;
            color: #fff;
            border: 1px solid #0d9488;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .pft-template-badge i { font-size: 9px; }

        .pft-template-badge-manual {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
        }

        /* ── Inline add-folder/add-file icons ────────────────────────────────── */
        .pft-row-actions {
            display: flex;
            align-items: center;
            gap: 1px;
            margin-left: 8px;
            flex-shrink: 0;
        }

        .pft-row-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border: none;
            background: transparent;
            font-size: 15px;
            cursor: pointer;
            transition: color .13s, transform .1s;
        }

        .pft-row-action-btn:hover {
            transform: translateY(-1px);
        }

        .pft-row-action-btn[data-row-add-folder] {
            color: #d97706;
        }

        .pft-row-action-btn[data-row-add-folder]:hover {
            color: #b45309;
        }

        .pft-row-action-btn[data-row-add-file] {
            color: #14a1bd;
        }

        .pft-row-action-btn[data-row-add-file]:hover {
            color: #0f7e93;
        }

        .pft-children {
            display: none;
            margin-left: 22px;
            border-left: 1px dashed #e2e8f0;
            padding-left: 6px;
        }

        .pft-node.open > .pft-children { display: block; }

        /* ── Add Folder / Add File modals ──────────────────────────────────── */
        .pft-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            z-index: 2000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pft-modal {
            width: 100%;
            max-width: 380px;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,.25);
        }

        .pft-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            background: #2D3E50;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
        }

        .pft-modal-close {
            background: none;
            border: none;
            color: #fff;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            opacity: .8;
        }

        .pft-modal-close:hover { opacity: 1; }

        .pft-modal-body { padding: 18px; }

        .pft-modal-target {
            font-size: 12.5px;
            color: #64748b;
            margin: 0 0 14px;
        }

        .pft-modal-target span { font-weight: 700; color: #334155; }

        .pft-file-drop {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            border: 1.5px dashed #cbd5e1;
            border-radius: 10px;
            background: #f8fafc;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }

        .pft-file-drop:hover {
            border-color: #14a1bd;
            background: #f0fdfe;
        }

        .pft-file-input-native {
            position: absolute;
            width: 1px;
            height: 1px;
            opacity: 0;
            overflow: hidden;
        }

        .pft-file-drop-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e0f7fa;
            color: #14a1bd;
            font-size: 15px;
            flex-shrink: 0;
        }

        .pft-file-drop-text {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .pft-file-drop-title {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .pft-file-drop-sub {
            font-size: 11.5px;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .pft-modal-error {
            font-size: 12px;
            color: #dc2626;
            margin-top: 6px;
            min-height: 14px;
        }

        .pft-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding: 14px 18px;
            border-top: 1px solid #f1f5f9;
        }
    </style>
@endpush

@push('script')
    <script>
        $(function() {
            $('#projectEditForm').on('submit', function() {
                $('#submitBtn').html('<i class="fa-solid fa-spinner fa-spin"></i> Saving…').prop('disabled', true);
            });

            $('#deleteProjectBtn').on('click', function() {
                Swal.fire({
                    title: 'Delete Project?',
                    html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash"></i></div>"{{ addslashes($project->name) }}" will be permanently removed.',
                    width: '380px', showCancelButton: true,
                    confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel', customClass: { popup: 'swal-theme' }, reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) $('#deleteProjectForm').submit();
                });
            });

            /* ── Project Files tree: expand/collapse ──────────────────────────── */
            $(document).on('click', '.pft-toggle', function(e) {
                e.stopPropagation();
                $(this).closest('.pft-node').toggleClass('open');
            });

            $('#pftExpandCollapseBtn').on('click', function() {
                const $btn = $(this);
                const expanding = $btn.data('expanded') !== true;
                $('#pftTree .pft-node.has-children').toggleClass('open', expanding);
                $btn.data('expanded', expanding);
                $btn.html(expanding ?
                    '<i class="fa-solid fa-angle-double-up"></i> Collapse All' :
                    '<i class="fa-solid fa-angle-double-down"></i> Expand All');
            });

            /* ── Modal open/close helpers ─────────────────────────────────────── */
            function openModal(id) { $('#' + id).removeClass('d-none'); }
            function closeModal(id) {
                $('#' + id).addClass('d-none');
                $('.pft-item-active').removeClass('pft-item-active');
            }

            const projectId = {{ $project->id }};
            const csrfToken = '{{ csrf_token() }}';

            // Defaults to the project root — the header's Add Folder/Add File
            // buttons target the project itself, while the per-row icons on a
            // manual (non-template) folder override this to that folder's id.
            let addTargetFolderId = projectId;

            const projectName = @json($project->name);

            $('#pftAddFolderBtn').on('click', function() {
                addTargetFolderId = projectId;
                $('#pftNewFolderName').val('');
                $('#pftAddFolderError').text('');
                $('#pftAddFolderTarget').text(projectName);
                openModal('pftAddFolderModal');
            });

            $('#pftAddFileBtn').on('click', function() {
                addTargetFolderId = projectId;
                $('#pftNewFileInput').val('');
                $('#pftFileDropSub').text('No file chosen');
                $('#pftAddFileError').text('');
                $('#pftAddFileTarget').text(projectName);
                openModal('pftAddFileModal');
            });

            $(document).on('click', '[data-row-add-folder]', function(e) {
                e.stopPropagation();
                addTargetFolderId = $(this).data('row-add-folder');
                $('#pftNewFolderName').val('');
                $('#pftAddFolderError').text('');
                $('.pft-item-active').removeClass('pft-item-active');
                $(this).closest('.pft-item').addClass('pft-item-active');
                $('#pftAddFolderTarget').text($(this).closest('.pft-item').find('.pft-name').first().text().trim());
                openModal('pftAddFolderModal');
            });

            $(document).on('click', '[data-row-add-file]', function(e) {
                e.stopPropagation();
                addTargetFolderId = $(this).data('row-add-file');
                $('#pftNewFileInput').val('');
                $('#pftFileDropSub').text('No file chosen');
                $('#pftAddFileError').text('');
                $('.pft-item-active').removeClass('pft-item-active');
                $(this).closest('.pft-item').addClass('pft-item-active');
                $('#pftAddFileTarget').text($(this).closest('.pft-item').find('.pft-name').first().text().trim());
                openModal('pftAddFileModal');
            });

            $('[data-modal-close]').on('click', function() {
                closeModal($(this).data('modal-close'));
            });

            /* ── Create Folder ─────────────────────────────────────────────────── */
            // A toast fired right before window.location.reload() never
            // gets seen (the DOM is wiped) — stash it in sessionStorage and
            // fire it on the next page load instead.
            var pendingToast = sessionStorage.getItem('pft_toast');
            if (pendingToast) {
                sessionStorage.removeItem('pft_toast');
                try {
                    var t = JSON.parse(pendingToast);
                    if (typeof showToast === 'function') showToast(t.message, t.type);
                } catch (e) {}
            }

            function reloadWithToast(message, type) {
                sessionStorage.setItem('pft_toast', JSON.stringify({
                    message: message,
                    type: type
                }));
                window.location.reload();
            }

            $('#pftAddFolderConfirm').on('click', function() {
                const name = $('#pftNewFolderName').val().trim();
                if (!name) {
                    $('#pftAddFolderError').text('Folder name is required.');
                    return;
                }
                const $btn = $(this).prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> Creating…');
                $.ajax({
                    url: '{{ route('folders.store') }}',
                    method: 'POST',
                    data: { name: name, parent_id: addTargetFolderId, parent_type: 'folder', _token: csrfToken },
                }).done(function(res) {
                    if (res && res.success) {
                        reloadWithToast('Folder created successfully.', 'success');
                    } else {
                        $('#pftAddFolderError').text((res && res.message) || 'Could not create folder.');
                        if (typeof showToast === 'function') showToast((res && res.message) || 'Could not create folder.', 'danger');
                        $btn.prop('disabled', false).html('<i class="fa fa-floppy-disk"></i> Create Folder');
                    }
                }).fail(function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Could not create folder.';
                    $('#pftAddFolderError').text(msg);
                    if (typeof showToast === 'function') showToast(msg, 'danger');
                    $btn.prop('disabled', false).html('<i class="fa fa-floppy-disk"></i> Create Folder');
                });
            });

            $('#pftNewFileInput').on('change', function() {
                const files = this.files;
                if (!files.length) {
                    $('#pftFileDropSub').text('No file chosen');
                } else if (files.length === 1) {
                    $('#pftFileDropSub').text(files[0].name);
                } else {
                    $('#pftFileDropSub').text(files.length + ' files selected');
                }
            });

            /* ── Upload File(s) ───────────────────────────────────────────────── */
            $('#pftAddFileConfirm').on('click', function() {
                const files = $('#pftNewFileInput')[0].files;
                if (!files.length) {
                    $('#pftAddFileError').text('Please choose at least one file.');
                    return;
                }
                const formData = new FormData();
                for (let i = 0; i < files.length; i++) formData.append('file[]', files[i]);
                formData.append('parent_id', addTargetFolderId);
                formData.append('parent_type', 'folder');
                formData.append('_token', csrfToken);

                const $btn = $(this).prop('disabled', true).html(
                    '<i class="fa fa-spinner fa-spin"></i> Uploading…');
                $.ajax({
                    url: '{{ route('files.store') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                }).done(function() {
                    reloadWithToast('File uploaded successfully.', 'success');
                }).fail(function(xhr) {
                    var msg = xhr.responseJSON?.message || 'Upload failed.';
                    $('#pftAddFileError').text(msg);
                    if (typeof showToast === 'function') showToast(msg, 'danger');
                    $btn.prop('disabled', false).html('<i class="fa fa-cloud-arrow-up"></i> Upload');
                });
            });
        });
    </script>
@endpush
