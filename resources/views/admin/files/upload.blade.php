@extends('admin.layouts.app')
@section('title', 'Project Folders')
@section('page_title', 'Project Folders')

@section('content')

    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card">

            <div class="fb-layout">

                <section class="fb-main">

                    <div class="upload-card">
                        <h3 class="fb-page-title">Upload files / folders</h3>
                        <p class="text-muted" style="margin-top:6px;">Drag & drop files here or click to select. Multiple
                            files supported.</p>

                        <div id="uploadDropzone" class="upload-dropzone" tabindex="0">
                            <div class="dz-icon">+</div>
                            <div>
                                <div style="font-weight:700">Drop files here</div>
                                <div style="font-size:13px;color:#6b7280">or click to browse your computer</div>
                            </div>
                        </div>

                        <div class="upload-actions">
                            <input id="uploadInput" type="file" multiple style="display:none" webkitdirectory
                                directory />
                            <button id="btnSelect" class="btn-clear">Select files</button>
                            <button id="btnUpload" class="btn-upload">Upload All</button>
                            <button id="btnClear" class="btn-clear">Clear</button>
                            <div style="margin-left:auto;color:#475569;font-size:13px" id="totalCount">0 files</div>
                        </div>

                        <div id="uploadList" class="upload-list" aria-live="polite"></div>
                    </div>

                </section>
            </div>
        </div>
    </div>

@endsection

@push('addOnCss')
    <style>
        /* Upload area */
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
            transition: background 0.12s ease, border-color 0.12s ease, box-shadow 0.12s ease;
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
            box-shadow: 0 6px 18px rgba(99, 102, 241, 0.08) inset;
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
            box-shadow: 0 6px 18px rgba(14, 165, 233, 0.06);
        }

        .upload-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .btn-upload,
        .btn-clear {
            background: #2563eb;
            color: #fff;
            border: 0;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        .btn-clear {
            background: #f3f4f6;
            color: #374151;
            border: 1px solid #e5e7eb;
        }

        .upload-list {
            margin-top: 16px;
            display: grid;
            gap: 10px;
        }

        .upload-item {
            display: flex;
            gap: 12px;
            align-items: center;
            padding: 8px;
            border-radius: 8px;
            background: #fbfdff;
            border: 1px solid #eef2ff;
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
            flex-shrink: 0;
        }

        .upload-meta {
            flex: 1;
            min-width: 0;
        }

        .upload-meta .name {
            font-weight: 600;
            color: #0f172a;
            font-size: 13px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .upload-meta .size {
            font-size: 12px;
            color: #667085;
        }

        .upload-progress {
            height: 6px;
            background: #f1f5f9;
            border-radius: 6px;
            overflow: hidden;
            margin-top: 6px;
        }

        .upload-progress>i {
            display: block;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #06b6d4, #3b82f6);
        }

        .upload-item .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .upload-remove {
            background: transparent;
            border: 0;
            color: #ef4444;
            cursor: pointer;
            font-weight: 700;
        }

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

        /* .fb-layout {
                                                                                                                                                                                                                                                                                                                                                                                                        display: grid;
                                                                                                                                                                                                                                                                                                                                                                                                        grid-template-columns: 260px minmax(0, 1fr);
                                                                                                                                                                                                                                                                                                                                                                                                        gap: 14px;
                                                                                                                                                                                                                                                                                                                                                                                                    } */

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

        .fb-tree-label span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 180px;
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

        .fb-dropdown-menu {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            min-width: 160px;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            background: #fff;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
            display: none;
            z-index: 30;
        }

        .fb-dropdown-menu.open {
            display: block;
        }

        .fb-dropdown-menu button {
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            padding: 8px 10px;
            font-size: 12px;
            color: #334155;
        }

        .fb-dropdown-menu button:hover {
            background: #eff6ff;
            color: #1d4ed8;
        }



        .fb-bulk-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-size: 12px;
            color: #475569;
            flex-wrap: wrap;
        }

        .fb-selected-count {
            font-weight: 600;
            color: #1e293b;
        }

        .fb-col-check {
            width: 34px;
        }

        .fb-col-actions {
            width: 220px;
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
@endpush

@push('script')
    <script>
        (function() {
            const drop = document.getElementById('uploadDropzone');
            const input = document.getElementById('uploadInput');
            const btnSelect = document.getElementById('btnSelect');
            const btnUpload = document.getElementById('btnUpload');
            const btnClear = document.getElementById('btnClear');
            const listEl = document.getElementById('uploadList');
            const totalCount = document.getElementById('totalCount');

            let files = []; // {file, id, progress}

            function humanSize(bytes) {
                if (bytes === 0) return '0 B';
                const units = ['B', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(1024));
                return (bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0) + ' ' + units[i];
            }

            function renderList() {
                listEl.innerHTML = '';
                files.forEach(item => {
                    const id = item.id;
                    const f = item.file;
                    const el = document.createElement('div');
                    el.className = 'upload-item';

                    const thumb = document.createElement('div');
                    thumb.className = 'upload-thumb';

                    if (f.type.startsWith('image/')) {
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
                    removeBtn.className = 'upload-remove';
                    removeBtn.textContent = 'Remove';
                    removeBtn.addEventListener('click', () => {
                        files = files.filter(x => x.id !== id);
                        renderList();
                        updateCount();
                    });

                    actions.appendChild(removeBtn);

                    el.appendChild(thumb);
                    el.appendChild(meta);
                    el.appendChild(actions);

                    listEl.appendChild(el);
                });
            }

            function updateCount() {
                totalCount.textContent = `${files.length} file${files.length!==1?'s':''}`;
            }

            function addFiles(fileList) {
                const start = files.length ? files[files.length - 1].id + 1 : 1;
                Array.from(fileList).forEach((f, i) => {
                    files.push({
                        id: start + i,
                        file: f,
                        progress: 0
                    });
                });
                renderList();
                updateCount();
            }

            drop.addEventListener('click', () => input.click());
            btnSelect.addEventListener('click', (e) => {
                e.preventDefault();
                input.click();
            });

            input.addEventListener('change', (e) => {
                if (e.target.files && e.target.files.length) {
                    addFiles(e.target.files);
                    e.target.value = '';
                }
            });

            drop.addEventListener('dragenter', (e) => {
                e.preventDefault();
                drop.classList.add('dragover');
            });
            drop.addEventListener('dragover', (e) => {
                e.preventDefault();
                drop.classList.add('dragover');
            });
            drop.addEventListener('dragleave', (e) => {
                e.preventDefault();
                drop.classList.remove('dragover');
            });
            drop.addEventListener('drop', (e) => {
                e.preventDefault();
                drop.classList.remove('dragover');
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                    addFiles(e.dataTransfer.files);
                }
            });

            btnClear.addEventListener('click', (e) => {
                e.preventDefault();
                files = [];
                renderList();
                updateCount();
            });

            function uploadOne(item) {
                return new Promise((resolve) => {
                    const xhr = new XMLHttpRequest();
                    const fd = new FormData();
                    fd.append('file', item.file);
                    // include parent folder id when provided via query param (used when embedded in modal/iframe)
                    try {
                        const params = new URLSearchParams(window.location.search);
                        const parentId = params.get('parent_id');
                        if (parentId) fd.append('parent_id', parentId);
                    } catch (err) {
                        // ignore
                    }
                    // adjust URL to your upload endpoint
                    const url = '/admin/files/upload';
                    xhr.open('POST', url, true);
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
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
                if (!files.length) return;
                btnUpload.disabled = true;
                for (const item of files) {
                    // skip if already uploaded
                    if (item.progress >= 100) continue;
                    await uploadOne(item);
                }
                btnUpload.disabled = false;
            }

            btnUpload.addEventListener('click', (e) => {
                e.preventDefault();
                uploadAll();
            });

            // expose for debugging
            window._fbUpload = {
                addFiles,
                files
            };
        })();
    </script>
@endpush
