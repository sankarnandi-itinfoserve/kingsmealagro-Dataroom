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

                        <button type="button" class="fb-tool-btn" id="archiveBulkRestoreBtn">
                            <i class="fa-solid fa-trash-arrow-up"></i> Restore Selected
                        </button>
                    </div>

                    <div class="table-responsive">
                        <table class="table fb-table align-middle mb-0" id="archiveTable">
                            <thead>
                                <tr>
                                    <th class="fb-col-check"></th>
                                    <th>Name</th>
                                    <th>Archived On</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="archiveListBody">
                                @forelse ($projects as $project)
                                    <tr class="archive-row" data-name="{{ strtolower($project->name) }}">
                                        <td><input type="checkbox" class="fb-row-check archive-check" data-id="{{ $project->id }}"></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-semibold text-dark" style="font-size:13.5px;">{{ $project->name }}</span>
                                            </div>
                                            @if ($project->creator)
                                                <div class="text-muted" style="font-size:11.5px;">
                                                    by {{ trim($project->creator->fname . ' ' . $project->creator->lname) }}
                                                </div>
                                            @endif
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
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="fa-solid fa-box-archive fa-2x mb-2 d-block opacity-25"></i>
                                            No archived folders.
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
        .prj-restore-deleted-btn {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 8px;
            border: 1.5px solid #dc2626;
            background: #fef2f2;
            color: #dc2626;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background .13s, color .13s;
        }

        .prj-restore-deleted-btn:hover {
            background: #dc2626;
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
                    html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash-arrow-up"></i></div>"' +
                        name + '" will be recovered and moved back to Active.',
                    width: '380px',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
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
                    html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash-arrow-up"></i></div>Selected folders will be recovered and moved back to Active.',
                    width: '380px',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
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
