@extends('admin.layouts.app')

@section('title', 'Folder Archive')
@section('page_title', 'Folder Archive')

@section('content')

    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card">

            {{-- Header row --}}
            <div class="fb-header-row">
                <div>
                    <div class="fb-nav-line">
                        <nav class="fb-breadcrumb" aria-label="Breadcrumb">
                            <a href="{{ route('projects.index') }}" class="fb-crumb">Folders Management</a>
                            <span class="fb-crumb-sep">&gt;</span>
                            <span class="fb-crumb-current">Folder Archive</span>
                        </nav>
                    </div>
                </div>

                <div class="fb-header-actions">
                    <div class="fb-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="archiveSearchInput" placeholder="Search archived folders…">
                    </div>

                    <a href="{{ route('projects.index') }}" class="fb-tool-btn">
                        <i class="fa-solid fa-arrow-left"></i> Back to Folders
                    </a>
                </div>
            </div>

            <p class="fb-archive-subtitle">
                Deleted folders and files land here. Restoring puts them back exactly where they were.
            </p>

            <div class="fb-layout">
                <section class="fb-main">

                    {{-- Bulk-select toolbar --}}
                    <div class="fb-bulk-row">
                        <label class="fb-subscribe-label">
                            <input type="checkbox" id="archiveSelectAll">
                            Select all
                        </label>
                        <span class="fb-sel-badge" id="archiveSelectedCount">0 selected</span>

                        <span class="fb-toolbar-sep"></span>

                        <button type="button" class="fb-tool-btn fb-tool-btn-success-soft" id="archiveBulkRestoreBtn">
                            <i class="fa-solid fa-trash-arrow-up"></i> Restore Selected
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table fb-table align-middle mb-0" id="archiveTable">
                            <thead>
                                <tr>
                                    <th class="fb-col-check"></th>
                                    <th>Name</th>
                                    <th>Was In</th>
                                    <th>Deleted On</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="archiveListBody">
                                @forelse ($projects as $project)
                                    @php
                                        $isRoot = is_null($project->parent_item_id);
                                        $fullPath = $isRoot
                                            ? null
                                            : collect($project->getBreadcrumb())->reject(fn($n) => $n->id === $project->id)->pluck('name')->implode(' / ');

                                        if ($project->type === 'file') {
                                            $ext = strtolower(pathinfo($project->name, PATHINFO_EXTENSION));
                                            [$rowIcon, $rowIconColor] = match (true) {
                                                in_array($ext, ['doc', 'docx']) => ['fa-file-word', '#2563eb'],
                                                in_array($ext, ['xls', 'xlsx']) => ['fa-file-excel', '#16a34a'],
                                                in_array($ext, ['ppt', 'pptx']) => ['fa-file-powerpoint', '#ea580c'],
                                                $ext === 'pdf' => ['fa-file-pdf', '#dc2626'],
                                                in_array($ext, ['png', 'jpg', 'jpeg', 'gif']) => ['fa-file-image', '#7c3aed'],
                                                in_array($ext, ['zip', 'rar']) => ['fa-file-zipper', '#b45309'],
                                                default => ['fa-file', '#94a3b8'],
                                            };
                                        } else {
                                            $rowIcon = 'fa-folder';
                                            $rowIconColor = '#fbbf24';
                                        }
                                    @endphp
                                    <tr class="archive-row" data-name="{{ strtolower($project->name) }}">
                                        <td><input type="checkbox" class="fb-row-check archive-check" data-id="{{ $project->id }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="fa-solid {{ $rowIcon }}" style="color:{{ $rowIconColor }};font-size:13px;"></i>
                                                <span class="fw-semibold text-dark" style="font-size:13.5px;">{{ $project->name }}</span>
                                            </div>
                                            @if ($project->creator)
                                                <div class="text-muted" style="font-size:11.5px;">
                                                    by {{ trim($project->creator->fname . ' ' . $project->creator->lname) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="font-size:12.5px;max-width:260px;" class="text-muted text-truncate" title="{{ $isRoot ? '' : $fullPath }}">
                                            {{ $isRoot ? 'Root Folder' : ($fullPath !== '' ? $fullPath : '—') }}
                                        </td>
                                        <td style="font-size:13px;">
                                            {{ optional($project->deleted_at)->format('M d, Y') }}
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <form action="{{ route('projects.restoreDeleted', $project->id) }}" method="POST"
                                                      class="d-inline" data-restore-name="{{ $project->name }}">
                                                    @csrf
                                                    <button type="button" class="prj-restore-deleted-btn" title="Restore">
                                                        <i class="fa-solid fa-trash-arrow-up me-1"></i> Restore
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-box-archive fa-2x mb-2 d-block opacity-25"></i>
                                            No deleted items.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($projects->hasPages())
                        <div class="d-flex justify-content-end px-2 pb-2">
                            {{ $projects->links() }}
                        </div>
                    @endif

                </section>
            </div>
        </div>
    </div>

@endsection

@push('addOnCss')
    <style>
        .fb-archive-subtitle {
            margin: -6px 0 14px;
            font-size: 12.5px;
            color: #94a3b8;
        }

        .fb-tool-btn-success-soft {
            background: #f0fdf4 !important;
            border-color: #bbf7d0 !important;
            color: #16a34a !important;
            text-decoration: none !important;
        }

        .fb-tool-btn-success-soft:hover {
            background: #dcfce7 !important;
            border-color: #86efac !important;
            color: #15803d !important;
        }

        .prj-restore-deleted-btn {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 8px;
            border: 1.5px solid #16a34a;
            background: #f0fdf4;
            color: #16a34a;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background .13s, color .13s;
        }

        .prj-restore-deleted-btn:hover {
            background: #16a34a;
            color: #fff;
        }
    </style>
@endpush

@push('script')
    <script>
        $(function() {

            /* ── Live search ─────────────────────────────────────────────────────── */
            $('#archiveSearchInput').on('input', function() {
                const q = $(this).val().toLowerCase().trim();
                $('.archive-row').each(function() {
                    $(this).toggle(($(this).data('name') || '').toString().includes(q));
                });
            });

            /* ── Bulk select (checkboxes + "Select all") ──────────────────────────── */
            function updateArchiveSelection() {
                const $visible = $('.archive-check:visible');
                const checkedCount = $visible.filter(':checked').length;
                $('#archiveSelectedCount').text(checkedCount + ' selected');
                $('#archiveSelectAll').prop('checked', $visible.length > 0 && checkedCount === $visible.length);
            }

            $(document).on('change', '.archive-check', updateArchiveSelection);

            $('#archiveSelectAll').on('change', function() {
                const checked = $(this).is(':checked');
                $('.archive-check:visible').prop('checked', checked);
                updateArchiveSelection();
            });

            function collectSelectedArchiveIds() {
                return $('.archive-check:checked').map(function() {
                    return $(this).data('id');
                }).get();
            }

            /* ── Restore with SweetAlert (single row) ─────────────────────────────── */
            $(document).on('click', '.prj-restore-deleted-btn', function() {
                const form = $(this).closest('form');
                const name = form.data('restore-name');
                Swal.fire({
                    title: 'Restore Folder?',
                    html: '<div class="swal-theme-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-trash-arrow-up"></i></div>"' +
                        name + '" will be recovered and moved back to Active.',
                    width: '380px',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    confirmButtonText: 'Yes, restore',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-theme'
                    },
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) form.submit();
                });
            });

            /* ── Bulk restore ──────────────────────────────────────────────────────── */
            const restoreUrlTpl = "{{ route('projects.restoreDeleted', '__ID__') }}";
            const csrfToken = "{{ csrf_token() }}";

            $('#archiveBulkRestoreBtn').on('click', function() {
                const ids = collectSelectedArchiveIds();
                if (!ids.length) {
                    alert('Please select at least one folder.');
                    return;
                }

                Swal.fire({
                    title: 'Restore ' + ids.length + ' folder(s)?',
                    html: '<div class="swal-theme-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-trash-arrow-up"></i></div>Selected folders will be recovered and moved back to Active.',
                    width: '380px',
                    showCancelButton: true,
                    confirmButtonColor: '#16a34a',
                    confirmButtonText: 'Yes, restore them',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-theme'
                    },
                    reverseButtons: true,
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    let done = 0,
                        failed = 0;

                    ids.forEach(function(id) {
                        $.ajax({
                            url: restoreUrlTpl.replace('__ID__', id),
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            },
                        }).fail(function() {
                            failed++;
                        }).always(function() {
                            done++;
                            if (done === ids.length) {
                                showToast(
                                    failed > 0 ?
                                    (failed + ' of ' + ids.length + ' item(s) failed.') :
                                    (ids.length + ' folder(s) restored.'),
                                    failed > 0 ? 'danger' : 'success'
                                );
                                setTimeout(function() {
                                    window.location.reload();
                                }, 900);
                            }
                        });
                    });
                });
            });

        });
    </script>
@endpush
