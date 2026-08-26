@extends('admin.layouts.app')

@section('title', $file->name ?? 'Preview')
@section('page_title', 'File Preview')

@section('content')

    @php
        $ext = strtolower(pathinfo($file->name ?? '', PATHINFO_EXTENSION));
        $iconMap = [
            'pdf' => ['fa-file-pdf', '#dc2626'],
            'doc' => ['fa-file-word', '#2563eb'],
            'docx' => ['fa-file-word', '#2563eb'],
            'xls' => ['fa-file-excel', '#16a34a'],
            'xlsx' => ['fa-file-excel', '#16a34a'],
            'ppt' => ['fa-file-powerpoint', '#ea580c'],
            'pptx' => ['fa-file-powerpoint', '#ea580c'],
            'png' => ['fa-file-image', '#7c3aed'],
            'jpg' => ['fa-file-image', '#7c3aed'],
            'jpeg' => ['fa-file-image', '#7c3aed'],
            'gif' => ['fa-file-image', '#7c3aed'],
            'zip' => ['fa-file-zipper', '#b45309'],
            'rar' => ['fa-file-zipper', '#b45309'],
            'txt' => ['fa-file-lines', '#64748b'],
        ];
        [$icon, $iconColor] = $iconMap[$ext] ?? ['fa-file', '#64748b'];
        $encodedId = base64_encode($file->id);
        $breadcrumbFolders = collect($file->getBreadcrumb())
            ->slice(0, -1)
            ->values();
    @endphp

    <div class="pv-page">

        {{-- ── Top bar ── --}}
        <div class="pv-topbar">

            <div class="pv-file-info">
                <span class="pv-file-icon" style="color:{{ $iconColor }}; background:{{ $iconColor }}1a;">
                    <i class="fa-solid {{ $icon }}"></i>
                </span>
                <div class="pv-file-text">
                    <p class="pv-file-name">
                        <span class="pv-file-name-text">{{ $file->name }}</span>
                        @if ($previewUrl)
                            <span class="pv-readonly-badge"><i class="fa-solid fa-eye"></i> Read-only</span>
                        @endif
                        @if ($file->updated_at)
                            <span class="pv-file-meta">Last modified {{ $file->updated_at->format('d M Y, h:i A') }}</span>
                        @endif
                    </p>
                    @if ($breadcrumbFolders->isNotEmpty())
                        <p class="pv-file-path">
                            @foreach ($breadcrumbFolders as $i => $folder)
                                @if ($i > 0)
                                    <span class="pv-file-path-sep">/</span>
                                @endif
                                <a href="{{ route('shared.folders') }}#path={{ $breadcrumbFolders->slice(0, $i + 1)->pluck('id')->implode(',') }}"
                                    class="pv-file-path-link">{{ $folder->name }}</a>
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>

            <div class="pv-actions">
                <button type="button" class="pv-action-btn" onclick="history.back()">
                    <i class="fa-solid fa-arrow-left"></i> Back
                </button>
                <a href="{{ route('files.preview', $encodedId) }}" class="pv-action-btn">
                    <i class="fa-solid fa-arrow-rotate-right"></i> Refresh
                </a>
                <a href="/files/{{ $encodedId }}/download/file" class="pv-action-btn">
                    <i class="fa-solid fa-download"></i> Download
                </a>
                <button type="button" class="pv-action-btn pv-action-edit" id="pvEditBtn" onclick="openEditPanel()">
                    <i class="fa-solid fa-pen-to-square"></i> Edit
                </button>
                <button type="button" class="pv-action-btn" id="pvFullPageBtn" onclick="toggleFullPage()" title="Full page">
                    <i class="fa-solid fa-expand"></i> Full Page
                </button>
            </div>

        </div>

        {{-- ── Breadcrumb ── --}}
        @php
            // Build comma-separated ID path for each folder in the breadcrumb.
            // The hash format is #path=id1,id2,... (folders only, not the file itself).
            $folderCrumbs = collect($breadcrumb)->filter(fn($c) => $c->type === 'folder')->values();
        @endphp
        {{-- <nav class="pv-breadcrumb" aria-label="File location">
            <a href="{{ route('shared.folders') }}" class="pv-bc-item pv-bc-home">
                <i class="fa-solid fa-folder-tree me-1"></i>Project Folders
            </a>
            @foreach ($breadcrumb as $crumb)
                <span class="pv-bc-sep"><i class="fa-solid fa-chevron-right"></i></span>
                @if (!$loop->last)
                    @php
                        // IDs of all folders up to and including this one
                        $pathIds = $folderCrumbs->take($loop->index + 1)->pluck('id')->implode(',');
                    @endphp
                    <a href="{{ route('shared.folders') }}#path={{ $pathIds }}" class="pv-bc-item" title="{{ $crumb->name }}">
                        <i class="fa-solid fa-folder me-1" style="font-size:11px;color:#f59e0b;"></i>{{ Str::limit($crumb->name, 28) }}
                    </a>
                @else
                    <span class="pv-bc-item pv-bc-current" title="{{ $crumb->name }}">
                        <i class="fa-solid fa-file me-1" style="color:{{ $iconColor }};font-size:11px;"></i>{{ Str::limit($crumb->name, 32) }}
                    </span>
                @endif
            @endforeach
        </nav> --}}

        {{-- ── Edit workflow panel — download the file, edit it locally,
             then re-upload it here (step 1/2 below) ── --}}
        <div id="pvEditPanel" class="pv-edit-panel d-none">

                {{-- Step indicators --}}
                <div class="pv-steps">
                    <div class="pv-step" id="pvStep1">
                        <div class="pv-step-num">1</div>
                        <div class="pv-step-body">
                            <div class="pv-step-title">Download & Edit</div>
                            <div class="pv-step-sub">Download the file, edit it on your computer, then come back here.</div>
                            <a href="/files/{{ $encodedId }}/download/file" class="pv-step-btn" id="pvDownloadEditBtn"
                                onclick="markDownloaded()">
                                <i class="fa-solid fa-download me-1"></i> Download {{ $file->name }}
                            </a>
                        </div>
                    </div>

                    <div class="pv-step-arrow"><i class="fa-solid fa-chevron-right"></i></div>

                    <div class="pv-step" id="pvStep2">
                        <div class="pv-step-num">2</div>
                        <div class="pv-step-body">
                            <div class="pv-step-title">Upload Edited File</div>
                            <div class="pv-step-sub">Select your edited file — the app will save it automatically.</div>
                        </div>
                    </div>

                    <div class="pv-step-arrow"><i class="fa-solid fa-chevron-right"></i></div>

                    <div class="pv-step" id="pvStep3">
                        <div class="pv-step-num">3</div>
                        <div class="pv-step-body">
                            <div class="pv-step-title">Saved</div>
                            <div class="pv-step-sub">Your changes are saved and the file has been updated.</div>
                        </div>
                    </div>
                </div>

                {{-- Upload area --}}
                @if (session('success'))
                    <div class="pv-alert pv-alert-success mx-4 mb-3"><i
                            class="fa-solid fa-circle-check me-1"></i>{{ session('success') }}
                    </div>
                @elseif (session('error'))
                    <div class="pv-alert pv-alert-error mx-4 mb-3"><i
                            class="fa-solid fa-circle-exclamation me-1"></i>{{ session('error') }}</div>
                @endif

                <form action="{{ route('files.update', $encodedId) }}" method="POST" enctype="multipart/form-data"
                    id="updateForm">
                    @csrf
                    <div class="pv-upload-row">
                        <div class="pv-drop-zone" id="pvDropZone">
                            <i class="fa-solid fa-cloud-arrow-up pv-drop-icon"></i>
                            <p class="pv-drop-title">Drop edited file here or <span class="pv-drop-link">browse</span></p>
                            <p class="pv-drop-hint">Will replace <strong>{{ $file->name }}</strong></p>
                            <input type="file" name="file" id="pvFileInput" class="pv-file-hidden" required>
                        </div>
                        <div id="pvFileChosen" class="pv-file-chosen d-none">
                            <i class="fa-solid fa-file me-2" style="color:#253447"></i>
                            <span id="pvFileName"></span>
                            <button type="button" class="pv-file-clear" onclick="clearUpdateFile()">&times;</button>
                        </div>
                        <div class="pv-upload-actions">
                            <button type="button" class="pv-action-btn" onclick="closeEditPanel()">Cancel</button>
                            <button type="submit" class="pv-action-btn pv-action-sp" id="pvSubmitBtn" disabled>
                                <i class="fa-solid fa-cloud-arrow-up me-1"></i> Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        {{-- ── Preview frame ── --}}
        <div class="pv-frame-wrap" id="pvFrameWrap">
            @if ($previewUrl)
                <iframe src="{{ $previewUrl }}" id="pvPreviewFrame" class="pv-iframe" allowfullscreen></iframe>
            @else
                <div class="pv-unavailable">
                    <i class="fa-solid {{ $icon }} pv-unavail-icon" style="color:{{ $iconColor }}"></i>
                    <p class="pv-unavail-title">Preview not available</p>
                    <p class="pv-unavail-sub">This file type cannot be previewed in the browser.</p>
                    <a href="/files/{{ $encodedId }}/download/file" class="pv-action-btn mt-3">
                        <i class="fa-solid fa-download"></i> Download File
                    </a>
                </div>
            @endif
        </div>

    </div>

@endsection

@push('addOnCss')
    <style>
        /* ── Page shell ── */
        .pv-page {
            display: flex;
            flex-direction: column;
            height: calc(100vh - 60px);
            background: #f0f2f5;
            overflow: hidden;
        }

        /* ── Top bar ── */
        .pv-topbar {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px;
            height: 52px;
            background: #fff;
            border-bottom: 1px solid #e9eef6;
            box-shadow: 0 1px 3px rgba(37, 52, 71, .05);
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        /* File info */
        .pv-file-info {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .pv-file-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            font-size: 15px;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .pv-file-text {
            min-width: 0;
            line-height: 1.35;
        }

        .pv-file-name {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }

        .pv-file-name-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 420px;
        }

        .pv-readonly-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
            padding: 1px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .03em;
            text-transform: uppercase;
            background: #eff6ff;
            color: #e4394a;
            border: 1px solid #bfdbfe;
        }

        .pv-file-path {
            margin: 2px 0 0;
            font-size: 11px;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 520px;
        }

        .pv-file-meta {
            margin: 0;
            padding-left: 10px;
            border-left: 1px solid #e2e8f0;
            font-size: 11px;
            font-weight: 500;
            color: #94a3b8;
            white-space: nowrap;
        }

        /* Action buttons */
        .pv-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .pv-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            height: 30px;
            padding: 0 13px;
            border: 1px solid #dbe4f0;
            border-radius: 7px;
            background: #fff;
            color: #475569;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: border-color .14s, background .14s, color .14s;
        }

        .pv-action-btn:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
            color: #253447;
        }

        .pv-action-sp {
            background: #253447;
            color: #fff;
            border-color: #253447;
        }

        .pv-action-sp:hover {
            background: #1a2737;
            border-color: #1a2737;
            color: #fff;
        }

        .pv-action-edit {
            background: #0d9488;
            border-color: #05a194;
            color: #fff;
        }

        .pv-action-edit:hover {
            background: #05a194;
            border-color: #0d9488;
            color: #fff;
        }

        /* ── Full page mode: hide the sidebar/header/footer chrome so the
           document viewer fills the whole browser window. Scoped to body
           so it only ever affects this page's own layout. ── */
        body.pv-fullpage-mode .sidebar,
        body.pv-fullpage-mode .header,
        body.pv-fullpage-mode .footer {
            display: none !important;
        }

        body.pv-fullpage-mode .main-wrapper {
            margin-left: 0 !important;
        }

        body.pv-fullpage-mode .content {
            padding: 0 !important;
        }

        body.pv-fullpage-mode .pv-page {
            height: 100vh !important;
        }

        /* ── Breadcrumb ── */
        .pv-breadcrumb {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 2px;
            padding: 5px 18px;
            background: #f8fafc;
            border-bottom: 1px solid #e9eef6;
            flex-shrink: 0;
        }

        .pv-bc-item {
            font-size: 12px;
            font-weight: 500;
            color: #475569;
            text-decoration: none;
            padding: 2px 6px;
            border-radius: 5px;
            white-space: nowrap;
            transition: background .12s, color .12s;
        }

        .pv-bc-item:hover {
            background: #e9eef6;
            color: #1e293b;
        }

        .pv-bc-home {
            font-size: 13px;
            color: #64748b;
        }

        .pv-bc-home:hover {
            color: #253447;
        }

        .pv-bc-current {
            font-weight: 600;
            color: #1e293b;
            cursor: default;
        }

        .pv-bc-current:hover {
            background: transparent;
            color: #1e293b;
        }

        .pv-bc-sep {
            font-size: 9px;
            color: #cbd5e1;
            padding: 0 1px;
        }

        /* ── Edit workflow panel ── */
        .pv-edit-panel {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            flex-shrink: 0;
            padding: 18px 20px 16px;
        }

        .pv-steps {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }

        .pv-step {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            flex: 1;
            min-width: 180px;
        }

        .pv-step-num {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            flex-shrink: 0;
            background: #253447;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 2px;
        }

        .pv-step-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 2px;
        }

        .pv-step-sub {
            font-size: 11.5px;
            color: #64748b;
            line-height: 1.4;
        }

        .pv-step-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            height: 30px;
            padding: 0 14px;
            background: #253447;
            color: #fff;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: background .14s;
        }

        .pv-step-btn:hover {
            background: #1a2737;
            color: #fff;
        }

        .pv-step-arrow {
            color: #cbd5e1;
            font-size: 12px;
            padding-top: 8px;
            flex-shrink: 0;
        }

        .pv-upload-row {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .pv-upload-actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-shrink: 0;
        }


        .pv-drop-zone {
            flex: 1;
            border: 2px dashed #cbd5e1;
            border-radius: 10px;
            padding: 14px 20px;
            text-align: center;
            cursor: pointer;
            transition: border-color .15s, background .15s;
            min-width: 200px;
        }

        .pv-drop-zone:hover,
        .pv-drop-zone.dragover {
            border-color: #253447;
            background: #fff;
        }

        .pv-drop-icon {
            font-size: 22px;
            color: #94a3b8;
            margin-bottom: 6px;
        }

        .pv-drop-title {
            font-size: 13px;
            font-weight: 600;
            color: #334155;
            margin: 0 0 2px;
        }

        .pv-drop-link {
            color: #253447;
            text-decoration: underline;
            cursor: pointer;
        }

        .pv-drop-hint {
            font-size: 11px;
            color: #94a3b8;
            margin: 0;
        }

        .pv-file-hidden {
            display: none;
        }

        .pv-file-chosen {
            flex: 1;
            padding: 10px 14px;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            font-size: 13px;
            color: #1e293b;
            font-weight: 500;
            min-width: 200px;
        }

        .pv-file-clear {
            margin-left: auto;
            background: none;
            border: none;
            font-size: 17px;
            color: #94a3b8;
            cursor: pointer;
            padding: 0;
        }

        .pv-file-clear:hover {
            color: #ef4444;
        }

        .pv-alert {
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .pv-alert-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .pv-alert-error {
            background: #fff1f2;
            color: #be123c;
            border: 1px solid #fecdd3;
        }

        /* ── Preview frame ── */
        .pv-frame-wrap {
            flex: 1;
            overflow: hidden;
            margin: 14px 16px 14px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e9eef6;
            box-shadow: 0 2px 12px rgba(37, 52, 71, .07);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pv-iframe {
            width: 100%;
            height: 100%;
            border: 0;
            display: block;
            border-radius: 12px;
        }


        /* ── Unavailable state ── */
        .pv-unavailable {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 48px 24px;
            text-align: center;
        }

        .pv-unavail-icon {
            font-size: 52px;
            opacity: .6;
        }

        .pv-unavail-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e293b;
            margin: 8px 0 0;
        }

        .pv-unavail-sub {
            font-size: 13px;
            color: #94a3b8;
            margin: 0;
        }

        @media (max-width: 640px) {
            .pv-topbar {
                height: auto;
                padding: 10px 14px;
            }

            .pv-file-name {
                max-width: 200px;
            }

            .pv-action-sp span {
                display: none;
            }
        }
    </style>
@endpush

@push('script')
    <script>
        (function() {
            const dropZone = document.getElementById('pvDropZone');
            const fileInput = document.getElementById('pvFileInput');
            const fileChosen = document.getElementById('pvFileChosen');
            const fileName = document.getElementById('pvFileName');
            const submitBtn = document.getElementById('pvSubmitBtn');

            if (!dropZone) return;

            dropZone.addEventListener('click', () => fileInput.click());
            dropZone.addEventListener('dragover', e => {
                e.preventDefault();
                dropZone.classList.add('dragover');
            });
            dropZone.addEventListener('dragleave', () => dropZone.classList.remove('dragover'));
            dropZone.addEventListener('drop', e => {
                e.preventDefault();
                dropZone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    fileInput.files = e.dataTransfer.files;
                    showChosen(e.dataTransfer.files[0].name);
                }
            });
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length) showChosen(fileInput.files[0].name);
            });

            function showChosen(name) {
                fileName.textContent = name;
                fileChosen.classList.remove('d-none');
                dropZone.classList.add('d-none');
                submitBtn.disabled = false;
            }

            window.clearUpdateFile = function() {
                fileInput.value = '';
                fileChosen.classList.add('d-none');
                dropZone.classList.remove('d-none');
                submitBtn.disabled = true;
            };

            window.openEditPanel = function() {
                document.getElementById('pvEditPanel')?.classList.remove('d-none');
                document.getElementById('pvEditBtn')?.classList.add('d-none');
            };

            window.closeEditPanel = function() {
                document.getElementById('pvEditPanel')?.classList.add('d-none');
                document.getElementById('pvEditBtn')?.classList.remove('d-none');
                clearUpdateFile();
            };

            window.markDownloaded = function() {
                const step1 = document.getElementById('pvStep1');
                if (step1) step1.querySelector('.pv-step-num').innerHTML =
                    '<i class="fa-solid fa-check" style="font-size:11px"></i>';
            };

            // Auto-open edit panel on flash messages
            @if (session('success') || session('error'))
                window.openEditPanel();
            @endif
        })();
    </script>

    <script>
        var PV_FULLPAGE_KEY = 'pv_fullpage_mode';

        function pvSetFullPageBtnLabel(isFull) {
            const btn = document.getElementById('pvFullPageBtn');
            if (!btn) return;
            btn.innerHTML = isFull ?
                '<i class="fa-solid fa-compress"></i> Exit Full Page' :
                '<i class="fa-solid fa-expand"></i> Full Page';
        }

        window.toggleFullPage = function() {
            const isFull = document.body.classList.toggle('pv-fullpage-mode');
            localStorage.setItem(PV_FULLPAGE_KEY, isFull ? '1' : '0');
            pvSetFullPageBtnLabel(isFull);
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.body.classList.contains('pv-fullpage-mode')) {
                toggleFullPage();
            }
        });

        // Stay in full page mode across a refresh — only clicking "Exit
        // Full Page" (or Escape) turns it back off.
        if (localStorage.getItem(PV_FULLPAGE_KEY) === '1') {
            document.body.classList.add('pv-fullpage-mode');
            pvSetFullPageBtnLabel(true);
        }
    </script>
@endpush
