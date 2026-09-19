@extends('admin.layouts.app')

@section('title', 'Create Folder')
@section('page_title', 'Create Folder')

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

    <div class="row g-4">

        {{-- ── Main form col ────────────────────────────────────────────────────── --}}
        <div class="col-lg-7 col-xl-6 mx-auto">
            <form action="{{ route('projects.store') }}" method="POST" id="projectCreateForm"
                enctype="multipart/form-data">
                @csrf

                {{-- Setup method (zip import or start empty) + Folder Details, in one card --}}
                <div class="prj-form-card">
                    <div class="prj-form-section-header">
                        <div class="prj-section-title">Folder Details</div>
                    </div>
                    <div class="prj-form-section-body">

                        <label class="prj-label">How do you want to set this up?</label>
                        <div class="prj-mode-toggle" role="radiogroup" aria-label="How to set up this folder">
                            <label class="prj-mode-option">
                                <input type="radio" name="setup_mode" value="zip" checked>
                                <span class="prj-mode-card">
                                    <i class="fa-solid fa-file-zipper"></i>
                                    <span class="prj-mode-title">Import from ZIP</span>
                                    <span class="prj-mode-sub">Recreates the zip's structure inside this
                                        folder</span>
                                </span>
                            </label>
                            <label class="prj-mode-option">
                                <input type="radio" name="setup_mode" value="manual">
                                <span class="prj-mode-card">
                                    <i class="fa-solid fa-folder-plus"></i>
                                    <span class="prj-mode-title">Start empty</span>
                                    <span class="prj-mode-sub">Add folders and files manually afterwards</span>
                                </span>
                            </label>
                        </div>

                        <div id="zipUploadRow" class="mt-4">
                            <label class="prj-label">
                                ZIP File <span class="text-danger">*</span>
                            </label>

                            <label for="zipInput" class="prj-dropzone" id="prjDropzone">
                                <input type="file" name="zip" id="zipInput" accept=".zip" class="prj-dropzone-input">
                                <div class="prj-dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
                                <div class="prj-dropzone-text">
                                    <strong>Click to upload</strong> or drag and drop
                                    <div class="prj-dropzone-sub">ZIP files only</div>
                                </div>
                            </label>

                            <div class="prj-file-chip d-none" id="prjZipChip">
                                <div class="prj-file-chip-icon"><i class="fa-solid fa-file-zipper"></i></div>
                                <div class="prj-file-chip-body">
                                    <div class="prj-file-chip-name" id="prjZipName"></div>
                                    <div class="prj-file-chip-size" id="prjZipSize"></div>
                                </div>
                                <button type="button" class="prj-file-chip-remove" id="prjZipRemove">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            @error('zip')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <p class="prj-zip-hint">The folder will be named after the zip file. Every folder and
                                file inside it will be recreated with the same structure, nested under it.
                            </p>
                        </div>

                        <div id="manualNameRow" class="d-none mt-4">
                            <label class="prj-label">
                                Folder Name <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" id="previewName"
                                class="prj-input @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                placeholder="" autocomplete="off">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Submit row --}}
                        <div class="prj-form-actions mt-4">
                            <button type="submit" class="prj-submit-btn" id="submitBtn">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                Create Folder
                            </button>
                            <a href="{{ route('projects.index') }}" class="prj-cancel-btn">
                                Cancel
                            </a>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Upload progress popup --}}
    <div id="prjUploadModal" class="prj-modal d-none">
        <div class="prj-modal-backdrop"></div>
        <div class="prj-upload-dialog">
            <div class="prj-upload-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <p class="prj-upload-title" id="prjUploadTitle">Uploading…</p>
            <div class="prj-upload-progress">
                <div class="prj-upload-progress-bar" id="prjUploadProgressBar"></div>
            </div>
            <p class="prj-upload-percent" id="prjUploadPercent">0%</p>
        </div>
    </div>

@endsection

@push('addOnCss')
    <style>
        /* ── Page header ──────────────────────────────────────────────────────────── */
        .prj-create-header {
            display: flex;
            align-items: center;
            gap: 16px;
            background: #fff;
            border-radius: 14px;
            padding: 18px 22px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .07);
        }

        .prj-create-header-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: #253447;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .prj-create-header-body {
            flex: 1;
            min-width: 0;
        }

        .prj-create-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 3px;
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

        .prj-back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #475569;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background .13s, border-color .13s, color .13s;
            flex-shrink: 0;
        }

        .prj-back-btn:hover {
            background: #253447;
            border-color: #253447;
            color: #fff;
        }

        /* ── Form cards ───────────────────────────────────────────────────────────── */
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

        /* ── Inputs ───────────────────────────────────────────────────────────────── */
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
            border-color: #253447;
            box-shadow: 0 0 0 3px rgba(37, 52, 71, .09);
        }

        .prj-input.is-invalid {
            border-color: #dc2626;
        }

        .prj-input.is-invalid:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .1);
        }

        select.prj-input {
            cursor: pointer;
        }

        /* ── Setup-mode toggle (manual vs zip import) ────────────────────────────────── */
        .prj-mode-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .prj-mode-option {
            display: block;
            cursor: pointer;
            margin: 0;
        }

        .prj-mode-option input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .prj-mode-card {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            padding: 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            transition: border-color .15s, box-shadow .15s, background .15s;
        }

        .prj-mode-card i {
            font-size: 18px;
            color: #64748b;
        }

        .prj-mode-title {
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
        }

        .prj-mode-sub {
            font-size: 11.5px;
            color: #94a3b8;
            line-height: 1.4;
        }

        .prj-mode-option input:checked+.prj-mode-card {
            border-color: #253447;
            background: #f8fafc;
            box-shadow: 0 0 0 3px rgba(37, 52, 71, .09);
        }

        .prj-mode-option input:checked+.prj-mode-card i {
            color: #253447;
        }

        .prj-zip-hint {
            font-size: 11.5px;
            color: #94a3b8;
            margin: 6px 0 0;
        }

        /* ── Modern dropzone (zip upload) ────────────────────────────────────────── */
        .prj-dropzone-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .prj-dropzone {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 30px 20px;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            background: #f8fafc;
            cursor: pointer;
            text-align: center;
            transition: border-color .15s, background .15s;
        }

        .prj-dropzone:hover,
        .prj-dropzone.is-dragover {
            border-color: #253447;
            background: #f1f5f9;
        }

        .prj-dropzone.is-invalid {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .prj-dropzone-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #eef2ff;
            color: #253447;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .prj-dropzone-text {
            font-size: 13px;
            color: #64748b;
        }

        .prj-dropzone-text strong {
            color: #253447;
            font-weight: 700;
        }

        .prj-dropzone-sub {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* ── Selected-file chip (replaces the dropzone once a file is chosen) ──────── */
        .prj-file-chip {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #fff;
        }

        .prj-file-chip-icon {
            width: 38px;
            height: 38px;
            border-radius: 9px;
            background: #eef2ff;
            color: #253447;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .prj-file-chip-body {
            flex: 1;
            min-width: 0;
        }

        .prj-file-chip-name {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .prj-file-chip-size {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 1px;
        }

        .prj-file-chip-remove {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            border: none;
            background: #f1f5f9;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .15s, color .15s;
        }

        .prj-file-chip-remove:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        /* ── Upload progress popup ───────────────────────────────────────────────── */
        .prj-modal {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }

        .prj-modal.d-none {
            display: none;
        }

        .prj-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(2, 6, 23, .5);
            backdrop-filter: blur(2px);
        }

        .prj-upload-dialog {
            position: relative;
            width: 360px;
            max-width: calc(100% - 32px);
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 20px 60px rgba(2, 6, 23, .4);
            z-index: 2100;
            padding: 26px 24px 22px;
            text-align: center;
        }

        .prj-upload-icon {
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

        .prj-upload-title {
            margin: 0 0 14px;
            font-size: 13.5px;
            font-weight: 700;
            color: #1e293b;
        }

        .prj-upload-progress {
            height: 8px;
            background: #f1f5f9;
            border-radius: 6px;
            overflow: hidden;
        }

        .prj-upload-progress-bar {
            height: 100%;
            width: 0%;
            border-radius: 6px;
            background: linear-gradient(90deg, #06b6d4, #2563eb);
            transition: width .15s ease;
        }

        .prj-upload-percent {
            margin: 8px 0 0;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
        }

        @media (max-width: 575.98px) {
            .prj-mode-toggle {
                grid-template-columns: 1fr;
            }
        }

        .prj-input-group {
            position: relative;
        }

        .prj-input-prefix {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 12px;
            pointer-events: none;
            z-index: 1;
        }

        /* ── Submit row ───────────────────────────────────────────────────────────── */
        .prj-form-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 4px;
        }

        .prj-submit-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 28px;
            border-radius: 10px;
            background: #253447;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: background .13s, box-shadow .13s, transform .1s;
            box-shadow: 0 4px 14px rgba(37, 52, 71, .22);
        }

        .prj-submit-btn:hover {
            background: #1a2737;
            box-shadow: 0 6px 18px rgba(37, 52, 71, .28);
            transform: translateY(-1px);
        }

        .prj-submit-btn:active {
            transform: translateY(0);
        }

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

        .prj-cancel-btn:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
            color: #374151;
        }

        /* ── Sidebar cards ────────────────────────────────────────────────────────── */
        .prj-sidebar-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .06);
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }

        .prj-sidebar-card-header {
            padding: 13px 18px;
            font-size: 12.5px;
            font-weight: 700;
            color: #64748b;
            background: #f8fafc;
            border-bottom: 1px solid #f1f5f9;
            letter-spacing: .3px;
            text-transform: uppercase;
        }

        .prj-sidebar-card-body {
            padding: 18px;
        }

        /* ── Preview card ─────────────────────────────────────────────────────────── */
        .prj-preview-card {}

        .prj-preview-name {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
            line-height: 1.3;
            min-height: 22px;
            word-break: break-word;
        }

        .prj-preview-meta {
            font-size: 12.5px;
            color: #64748b;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .prj-preview-dates {
            font-size: 11.5px;
            color: #94a3b8;
        }

        /* ── Status badge (also used in index) ───────────────────────────────────── */
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
            background: #dcfce7;
            color: #16a34a;
        }

        .prj-status-closed {
            background: #fee2e2;
            color: #dc2626;
        }

        .prj-status-archived {
            background: #f3f4f6;
            color: #6b7280;
        }

        /* ── Checklist ────────────────────────────────────────────────────────────── */
        .prj-checklist {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .prj-checklist li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 18px;
            border-bottom: 1px solid #f8fafc;
        }

        .prj-checklist li:last-child {
            border-bottom: none;
        }

        .prj-check-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .prj-check-sp {
            background: #eff6ff;
            color: #2563eb;
        }

        .prj-check-db {
            background: #f0fdf4;
            color: #16a34a;
        }

        .prj-check-title {
            font-size: 13px;
            font-weight: 600;
            color: #1e293b;
        }

        .prj-check-sub {
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 1px;
        }

        /* ── Tip card ─────────────────────────────────────────────────────────────── */
        .prj-tip-card {}
    </style>
@endpush

@push('script')
    <script>
        $(function() {

            var $zipRow = $('#zipUploadRow');
            var $zipInput = $('#zipInput');
            var $dropzone = $('#prjDropzone');
            var $chip = $('#prjZipChip');
            var $chipName = $('#prjZipName');
            var $chipSize = $('#prjZipSize');
            var $nameRow = $('#manualNameRow');
            var $nameInput = $('#previewName');

            /* ── Setup-mode toggle (zip import vs manual) ─────────────────────────── */
            function syncSetupMode() {
                var mode = $('input[name="setup_mode"]:checked').val();
                var isZip = mode === 'zip';

                $zipRow.toggleClass('d-none', !isZip);
                $zipInput.prop('required', isZip);
                if (!isZip) {
                    clearZipSelection();
                }

                $nameRow.toggleClass('d-none', isZip);
                $nameInput.prop('required', !isZip);
            }

            $('input[name="setup_mode"]').on('change', syncSetupMode);
            syncSetupMode();

            /* ── Dropzone: selection preview, drag & drop, remove ─────────────────── */
            function formatBytes(bytes) {
                if (!bytes) return '0 KB';
                var units = ['B', 'KB', 'MB', 'GB'];
                var i = Math.floor(Math.log(bytes) / Math.log(1024));
                i = Math.max(0, Math.min(i, units.length - 1));
                return (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1) + ' ' + units[i];
            }

            function showZipSelection(file) {
                $chipName.text(file.name);
                $chipSize.text(formatBytes(file.size));
                $chip.removeClass('d-none');
                $dropzone.addClass('d-none');
            }

            function clearZipSelection() {
                $zipInput.val('');
                $chip.addClass('d-none');
                $dropzone.removeClass('d-none');
            }

            $zipInput.on('change', function() {
                var file = this.files && this.files[0];
                if (file) showZipSelection(file);
            });

            $('#prjZipRemove').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                clearZipSelection();
            });

            $dropzone.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('is-dragover');
            });
            $dropzone.on('dragleave', function() {
                $(this).removeClass('is-dragover');
            });
            $dropzone.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('is-dragover');
                var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
                if (files && files.length) {
                    $zipInput[0].files = files;
                    showZipSelection(files[0]);
                }
            });

            /* ── Field-error helpers (mirrors the framework's inline validation styling) ── */
            function clearFieldErrors() {
                $('.prj-input.is-invalid, .prj-dropzone.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();
            }

            function showFieldError(name, message) {
                if (name === 'zip') {
                    $dropzone.addClass('is-invalid');
                    $dropzone.after('<div class="invalid-feedback d-block">' + message + '</div>');
                    return;
                }
                var $field = $('[name="' + name + '"]');
                $field.addClass('is-invalid');
                $field.after('<div class="invalid-feedback d-block">' + message + '</div>');
            }

            /* ── AJAX submit with a real upload-progress popup ─────────────────────── */
            var $modal = $('#prjUploadModal');
            var $bar = $('#prjUploadProgressBar');
            var $percent = $('#prjUploadPercent');
            var $title = $('#prjUploadTitle');

            $('#projectCreateForm').on('submit', function(e) {
                e.preventDefault();
                clearFieldErrors();

                var $btn = $('#submitBtn');
                $btn.prop('disabled', true);

                $title.text('Uploading…');
                $bar.css('width', '0%');
                $percent.text('0%');
                $modal.removeClass('d-none');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: new FormData(this),
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {
                            xhr.upload.addEventListener('progress', function(evt) {
                                if (!evt.lengthComputable) return;
                                var pct = Math.round((evt.loaded / evt.total) * 100);
                                $bar.css('width', pct + '%');
                                $percent.text(pct + '%');
                                if (pct >= 100) {
                                    $title.text('Processing…');
                                }
                            });
                        }
                        return xhr;
                    },
                    success: function(response) {
                        $bar.css('width', '100%');
                        $percent.text('100%');
                        $modal.addClass('d-none');
                        showToast((response && response.message) || 'Folder created successfully.', 'success');
                        setTimeout(function() {
                            window.location.href = '{{ route('projects.index') }}';
                        }, 900);
                    },
                    error: function(xhr) {
                        $modal.addClass('d-none');
                        $btn.prop('disabled', false);

                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                showFieldError(field, messages[0]);
                            });
                        } else {
                            alert('Something went wrong while creating the folder. Please try again.');
                        }
                    }
                });
            });

        });
    </script>
@endpush
