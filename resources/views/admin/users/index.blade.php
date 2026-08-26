@extends('admin.layouts.app')
@section('title', 'Users')
@section('page_title', 'Users')

@section('content')

    @php
        // The checkbox column only exists for managers, shifting every
        // filterable column's DataTables index by one when present — keep
        // the data-col attributes below in sync with the JS columns array.
        $colOffset = auth()->user()->can('manage users') ? 1 : 0;
    @endphp

    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card usr-card">

            {{-- ── Card header ── --}}
            <div class="usr-card-header">
                <div class="usr-card-header-icon">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="flex-grow-1">
                    <nav class="fb-breadcrumb" aria-label="Breadcrumb">
                        <span style="color:#fff;font-size:15px;font-weight:700;">Users</span>
                    </nav>
                </div>
                <div class="usr-header-actions">
                    <div class="usr-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="userSearchInput" placeholder="Search users…">
                    </div>
                    @can('manage users')
                    <button type="button" class="usr-add-btn usr-add-btn-white" onclick="openModal('{{ route('users.create') }}')">
                        <i class="fa-solid fa-user-plus"></i> Add User
                    </button>
                    @endcan
                </div>
            </div>

            {{-- ── Bulk action bar — shown once at least one row is checked ── --}}
            @can('manage users')
                <div class="usr-bulk-bar d-none" id="usrBulkBar">
                    <span class="usr-bulk-count"><strong id="usrBulkCount">0</strong> selected</span>
                    <div class="usr-bulk-actions">
                        <button type="button" class="usr-bulk-btn" onclick="bulkFolderAccess()">
                            <i class="fa-solid fa-folder-open"></i> Folder Access
                        </button>
                        <button type="button" class="usr-bulk-btn usr-bulk-btn-danger" onclick="bulkDeleteUsers()">
                            <i class="fa-solid fa-trash-can"></i> Delete
                        </button>
                        <button type="button" class="usr-bulk-btn usr-bulk-btn-ghost" onclick="clearUserSelection()">
                            Clear
                        </button>
                    </div>
                </div>
            @endcan

            {{-- ── Stats strip ── --}}
            {{-- <div class="usr-stats-strip">
                <div class="usr-stat-item">
                    <span class="usr-stat-dot" style="background:#c0272d;"></span>
                    <span class="usr-stat-val" id="userCount">—</span>
                    <span class="usr-stat-label">Total Users</span>
                </div>
                <div class="usr-stat-divider"></div>
                <div class="usr-stat-item">
                    <span class="usr-stat-dot" style="background:#253447;"></span>
                    <span class="usr-stat-label">Manage roles inline via the dropdown</span>
                </div>
                <div class="usr-stat-divider"></div>
                <div class="usr-stat-item">
                    <span class="usr-stat-dot" style="background:#9b1c21;"></span>
                    <span class="usr-stat-label">Locked accounts shown with
                        <span class="usr-locked-chip" style="display:inline-flex;margin-left:4px;">
                            <i class="fa fa-lock me-1"></i>Locked
                        </span>
                    </span>
                </div>
            </div> --}}

            {{-- ── Table section ── --}}
            <section class="fb-main usr-table-section">
                <div class="table-responsive">
                    <table class="table usr-table align-middle" id="users-table">
                        <thead>
                            <tr>
                                @can('manage users')
                                    <th style="width:1%; white-space:nowrap;">
                                        <input type="checkbox" id="usrSelectAll" class="usr-row-check">
                                    </th>
                                @endcan
                                <th style="width:1%; white-space:nowrap;">#</th>
                                <th class="col-has-filter">
                                    <div class="col-th-inner">
                                        <span>User</span>
                                        <button type="button" class="col-filter-btn" data-col="{{ 1 + $colOffset }}" title="Filter"><i
                                                class="fa-solid fa-filter"></i></button>
                                    </div>
                                </th>
                                <th class="col-has-filter">
                                    <div class="col-th-inner">
                                        <span>Role</span>
                                        <button type="button" class="col-filter-btn" data-col="{{ 2 + $colOffset }}" title="Filter"><i
                                                class="fa-solid fa-filter"></i></button>
                                    </div>
                                </th>
                                <th class="col-has-filter">
                                    <div class="col-th-inner">
                                        <span>Email</span>
                                        <button type="button" class="col-filter-btn" data-col="{{ 3 + $colOffset }}" title="Filter"><i
                                                class="fa-solid fa-filter"></i></button>
                                    </div>
                                </th>
                                <th class="col-has-filter" style="white-space:nowrap;">
                                    <div class="col-th-inner">
                                        <span>Status</span>
                                        <button type="button" class="col-filter-btn" data-col="{{ 4 + $colOffset }}" title="Filter"><i
                                                class="fa-solid fa-filter"></i></button>
                                    </div>
                                </th>
                                <th style="white-space:nowrap;">Actions</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </section>
        </div>
    </div>

    {{-- ── Column filter panels (outside table so clicks don't bubble into <th>) ── --}}
    <div class="col-filter-panel" data-col-panel="{{ 1 + $colOffset }}">
        <div class="cfp-header">Filter by Name</div>
        <div class="cfp-body">
            <input type="text" class="cfp-input" placeholder="Search name…">
        </div>
        <div class="cfp-footer">
            <button type="button" class="cfp-reset" data-col="{{ 1 + $colOffset }}">Reset</button>
            <button type="button" class="cfp-apply" data-col="{{ 1 + $colOffset }}"><i class="fa-solid fa-check me-1"></i>Filter</button>
        </div>
    </div>

    <div class="col-filter-panel" data-col-panel="{{ 2 + $colOffset }}">
        <div class="cfp-header">Filter by Role</div>
        <div class="cfp-body cfp-checkboxes">
            @foreach ($roles as $role)
                <label class="cfp-check-label">
                    <input type="checkbox" value="{{ $role->name }}">
                    <span>{{ strtoupper(str_replace('-', ' ', $role->name)) }}</span>
                </label>
            @endforeach
        </div>
        <div class="cfp-footer">
            <button type="button" class="cfp-reset" data-col="{{ 2 + $colOffset }}">Reset</button>
            <button type="button" class="cfp-apply" data-col="{{ 2 + $colOffset }}"><i class="fa-solid fa-check me-1"></i>Filter</button>
        </div>
    </div>

    <div class="col-filter-panel cfp-right" data-col-panel="{{ 3 + $colOffset }}">
        <div class="cfp-header">Filter by Email</div>
        <div class="cfp-body">
            <input type="text" class="cfp-input" placeholder="Search email…">
        </div>
        <div class="cfp-footer">
            <button type="button" class="cfp-reset" data-col="{{ 3 + $colOffset }}">Reset</button>
            <button type="button" class="cfp-apply" data-col="{{ 3 + $colOffset }}"><i
                    class="fa-solid fa-check me-1"></i>Filter</button>
        </div>
    </div>

    <div class="col-filter-panel cfp-right" data-col-panel="{{ 4 + $colOffset }}">
        <div class="cfp-header">Filter by Status</div>
        <div class="cfp-body cfp-checkboxes">
            <label class="cfp-check-label">
                <input type="checkbox" value="active">
                <span>Active</span>
            </label>
            <label class="cfp-check-label">
                <input type="checkbox" value="inactive">
                <span>Inactive</span>
            </label>
        </div>
        <div class="cfp-footer">
            <button type="button" class="cfp-reset" data-col="{{ 4 + $colOffset }}">Reset</button>
            <button type="button" class="cfp-apply" data-col="{{ 4 + $colOffset }}"><i
                    class="fa-solid fa-check me-1"></i>Filter</button>
        </div>
    </div>

    {{-- ── Global AJAX Modal ── --}}
    <div class="modal fade" id="userModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content usr-modal" id="modalContent">
                <div class="usr-modal-loading">
                    <div class="usr-spinner"></div>
                    <p>Loading…</p>
                </div>
            </div>
        </div>
    </div>

@endsection


@push('addOnCss')
    <style>
        /* ── Row/select-all checkboxes ── */
        .usr-row-check {
            width: 16px;
            height: 16px;
            accent-color: #253447;
            cursor: pointer;
        }

        /* ── Bulk action bar ── */
        .usr-bulk-bar {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 10px 24px;
            background: #f0f5ff;
            border-bottom: 1px solid #dbe4f0;
        }

        .usr-bulk-count {
            font-size: 12.5px;
            color: #253447;
        }

        .usr-bulk-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .usr-bulk-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            font-size: 12px;
            font-weight: 600;
            color: #253447;
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all .15s;
        }

        .usr-bulk-btn:hover {
            background: #eef2f7;
        }

        .usr-bulk-btn-danger {
            color: #dc2626;
            border-color: #fecaca;
        }

        .usr-bulk-btn-danger:hover {
            background: #fef2f2;
        }

        .usr-bulk-btn-ghost {
            color: #64748b;
            background: transparent;
            border-color: transparent;
        }

        .usr-bulk-btn-ghost:hover {
            background: #e2e8f0;
        }

        /* ── Add User button (white pill, matches Add Template) ── */
        .usr-add-btn-white {
            color: #000;
            background: #fff;
            box-shadow: none;
        }

        .usr-add-btn-white:hover {
            opacity: .85;
            box-shadow: none;
        }

        /* ── Status badge ── */
        .usr-status-badge {
            display: inline-flex;
            align-items: center;
            font-size: 11.5px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
            white-space: nowrap;
        }

        .usr-status-active {
            background: #16a34a;
            color: #fff;
        }

        .usr-status-inactive {
            background: #dc2626;
            color: #fff;
        }


        /* ── Column filter trigger ── */
        .col-has-filter {
            position: relative;
        }

        .col-th-inner {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 6px;
            white-space: nowrap;
        }

        .col-filter-btn {
            background: none;
            border: none;
            padding: 3px 5px;
            cursor: pointer;
            color: #b0bec5;
            border-radius: 4px;
            font-size: 10px;
            line-height: 1;
            flex-shrink: 0;
            transition: all .15s;
        }

        .col-filter-btn:hover {
            color: #253447;
            background: rgba(37, 52, 71, .1);
        }

        .col-filter-btn.active {
            color: #c0272d;
            background: #fef2f2;
        }

        /* ── Dropdown panel ── */
        .col-filter-panel {
            display: none;
            position: fixed;
            width: 230px;
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 10px;
            box-shadow: 0 8px 28px rgba(37, 52, 71, .15);
            z-index: 9999;
            overflow: hidden;
        }

        .col-filter-panel.open {
            display: block;
        }

        .cfp-header {
            padding: 9px 13px 7px;
            font-size: 11px;
            font-weight: 700;
            color: #253447;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            letter-spacing: .03em;
            text-transform: uppercase;
        }

        .cfp-body {
            padding: 10px 13px;
            max-height: 190px;
            overflow-y: auto;
        }

        .cfp-input {
            width: 100%;
            border: 1px solid #dbe4f0;
            border-radius: 6px;
            padding: 7px 10px;
            font-size: 12px;
            color: #253447;
            outline: none;
            transition: border-color .15s;
        }

        .cfp-input:focus {
            border-color: #253447;
        }

        /* checkboxes */
        .cfp-checkboxes {
            padding: 8px 13px;
        }

        .cfp-check-label {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 6px 4px;
            cursor: pointer;
            font-size: 12px;
            color: #334155;
            border-radius: 5px;
            transition: background .1s;
        }

        .cfp-check-label:hover {
            background: #f1f5f9;
        }

        .cfp-check-label input[type="checkbox"] {
            accent-color: #253447;
            width: 16px;
            height: 16px;
            cursor: pointer;
            flex-shrink: 0;
        }

        /* footer */
        .cfp-footer {
            display: flex;
            gap: 6px;
            padding: 8px 12px;
            border-top: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .cfp-reset {
            flex: 1;
            padding: 6px 0;
            border: 1px solid #dbe4f0;
            border-radius: 6px;
            background: #fff;
            color: #64748b;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
        }

        .cfp-reset:hover {
            border-color: #94a3b8;
            color: #334155;
        }

        .cfp-apply {
            flex: 1;
            padding: 6px 0;
            border: none;
            border-radius: 6px;
            background: linear-gradient(135deg, #253447, #1a2737);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity .15s;
        }

        .cfp-apply:hover {
            opacity: .85;
        }

        #users-table_wrapper,
        #users-table_wrapper .dataTables_scroll,
        #users-table {
            width: 100% !important;
        }

        /* ── DataTable gradient spinner ── */
        #users-table_wrapper .dataTables_processing {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.92);
            border: none !important;
            box-shadow: 0 4px 24px rgba(37, 52, 71, .12);
            border-radius: 12px;
            padding: 20px 24px;
            z-index: 100;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* hide DT 1.13.x built-in 4-dot spinner */
        #users-table_wrapper .dataTables_processing>div,
        #users-table_wrapper .dataTables_processing>span {
            display: none !important;
        }

        /* our custom ring via pseudo-element */
        #users-table_wrapper .dataTables_processing::before {
            content: '';
            display: block;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: conic-gradient(from 0deg, transparent 0deg, #253447 210deg, #1a2737 270deg, transparent 270deg);
            -webkit-mask: radial-gradient(farthest-side, transparent 76%, #000 76%);
            mask: radial-gradient(farthest-side, transparent 76%, #000 76%);
            animation: dt-spin .75s linear infinite;
        }

        @keyframes dt-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush


@push('script')
    <script>
        $(function() {

            @if (session('status'))
                showToast(@json(session('status')), 'success');
            @elseif (session('error'))
                showToast(@json(session('error')), 'danger');
            @endif

            let canManage = @json(auth()->user()->can('manage users'));

            // ── Row selection (kept across pages/reloads via id set) ──
            let selectedUserIds = new Set();

            function updateBulkBar() {
                let n = selectedUserIds.size;
                $('#usrBulkCount').text(n);
                $('#usrBulkBar').toggleClass('d-none', n === 0);
                $('#usrSelectAll').prop('checked', n > 0 && $('.usr-row-check[data-id]').length > 0 &&
                    $('.usr-row-check[data-id]').length === $('.usr-row-check[data-id]:checked').length);
            }

            window.clearUserSelection = function() {
                selectedUserIds.clear();
                $('.usr-row-check[data-id]').prop('checked', false);
                $('#usrSelectAll').prop('checked', false);
                updateBulkBar();
            };

            $(document).on('change', '.usr-row-check[data-id]', function() {
                let id = Number($(this).data('id'));
                if (this.checked) selectedUserIds.add(id);
                else selectedUserIds.delete(id);
                updateBulkBar();
            });

            $('#usrSelectAll').on('change', function() {
                let checked = this.checked;
                $('.usr-row-check[data-id]').each(function() {
                    $(this).prop('checked', checked);
                    let id = Number($(this).data('id'));
                    if (checked) selectedUserIds.add(id);
                    else selectedUserIds.delete(id);
                });
                updateBulkBar();
            });

            window.bulkDeleteUsers = function() {
                let ids = [...selectedUserIds];
                if (!ids.length) return;

                Swal.fire({
                    title: `Delete ${ids.length} user${ids.length === 1 ? '' : 's'}?`,
                    html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash"></i></div>This action cannot be undone.',
                    width: '380px',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    confirmButtonText: 'Yes, delete!',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        popup: 'swal-theme'
                    },
                    reverseButtons: true,
                }).then(result => {
                    if (!result.isConfirmed) return;
                    $.ajax({
                        url: "{{ route('users.bulkDestroy') }}",
                        type: 'POST',
                        data: {
                            ids,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success(res) {
                            if (res.status === 'error') {
                                Swal.fire('Error', res.message, 'error');
                                return;
                            }
                            showToast(res.message, 'success');
                            clearUserSelection();
                            $('#users-table').DataTable().ajax.reload(null, false);
                        },
                        error(xhr) {
                            let msg = xhr.responseJSON?.message ?? 'Something went wrong.';
                            Swal.fire('Error!', msg, 'error');
                        }
                    });
                });
            };

            window.bulkFolderAccess = function() {
                let ids = [...selectedUserIds];
                if (!ids.length) return;

                let url = "{{ route('folder-access.bulkEditUsers') }}?user_ids=" + ids.join(',');
                $('#modalContent').html(`
                    <div class="usr-modal-loading">
                        <div class="usr-spinner"></div><p>Loading…</p>
                    </div>`);
                getUserModal().show();

                $.get(url)
                    .done(data => $('#modalContent').html(data))
                    .fail(() => $('#modalContent').html(`
                        <div class="usr-modal-loading">
                            <i class="fa fa-circle-exclamation fa-2x mb-3" style="color:#c0272d;"></i>
                            <p style="color:#253447;font-weight:600;">Failed to load folder access.</p>
                        </div>`));
            };

            // ── DataTable ──────────────────────────────
            let table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('users.index') }}",
                },
                pageLength: 20,
                order: canManage ? [
                    [1, 'desc']
                ] : [
                    [0, 'desc']
                ],
                responsive: true,
                autoWidth: false,

                drawCallback: function() {
                    let info = this.api().page.info();
                    $('#userCount').text(info.recordsTotal);
                    updateBulkBar();
                },

                columns: [
                    ...(canManage ? [{
                        data: 'id',
                        name: null,
                        orderable: false,
                        searchable: false,
                        render(data, type, row) {
                            let checked = selectedUserIds.has(row.id) ? 'checked' : '';
                            return `<input type="checkbox" class="usr-row-check" data-id="${row.id}" ${checked}>`;
                        }
                    }] : []),
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: true,
                        orderSequence: ['desc', 'asc'],
                        searchable: false
                    },
                    {
                        data: 'avatar',
                        name: 'avatar',
                        orderable: true,
                        orderSequence: ['asc', 'desc', ''],
                        searchable: true
                    },
                    {
                        data: 'role',
                        name: 'role',
                        orderable: false,
                        render(data, type, row) {
                            let cls = data === 'super-admin' ? 'super-admin' : (data === 'admin' ? 'admin' : '');
                            return `<span class="role-badge ${cls}">${data}</span>`;
                        }
                    },
                    {
                        data: 'email',
                        name: 'email',
                        orderSequence: ['asc', 'desc', '']
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: true,
                    },
                    {
                        data: 'actions',
                        name: null,
                        orderable: false,
                        searchable: false,
                        render(data, type, row) {
                            let btns = `
                                <a href="/analytics/users/${row.id}" target="_blank" class="user-action-btn btn-analytics" title="Analytics">
                                    <i class="fa fa-eye"></i>
                                </a>`;
                            if (canManage) {
                                btns += `<a href="/folder-access/user/${row.id}" class="user-action-btn btn-edit" title="Folder Access">
                                    <i class="fa-solid fa-folder-open"></i>
                                </a>`;
                                btns += `<button class="user-action-btn btn-edit" onclick="openModal('/users/${row.id}/edit')" title="Edit">
                                    <i class="fa fa-pen"></i>
                                </button>`;
                                if (row.deleted_at) {
                                    btns += `<button class="user-action-btn btn-restore" onclick="restoreUser(${row.id})" title="Restore">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>`;
                                } else {
                                    btns += `<button class="user-action-btn btn-del" onclick="deleteUser(${row.id})" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>`;
                                }
                                if (row.is_locked) {
                                    btns += `<button class="user-action-btn btn-unlock" onclick="unlockUser(${row.id})" title="Unlock">
                                         <i class="fa fa-unlock"></i>
                                     </button>`;
                                }
                            }
                            return `<div class="d-flex gap-1">${btns}</div>`;
                        }
                    },
                ],
            });

            // Wire search input
            $('#userSearchInput').on('keyup', function() {
                table.search(this.value).draw();
            });

            // ── Column Filters ─────────────────────────
            //
            // Direct binding (not delegation) so stopPropagation fires at
            // the button element, before the event ever reaches the <th>
            // where DataTables has its sort handler.
            //
            $('#users-table thead .col-filter-btn').on('click', function(e) {
                e.stopPropagation();
                let col = $(this).data('col');
                let panel = $('[data-col-panel="' + col + '"]');
                let isOpen = panel.hasClass('open');

                $('.col-filter-panel').removeClass('open');

                if (!isOpen) {
                    let rect = this.getBoundingClientRect();
                    let pw = 230;
                    let left = panel.hasClass('cfp-right') ? rect.right - pw : rect.left;

                    if (left + pw > window.innerWidth - 8) left = window.innerWidth - pw - 8;
                    if (left < 8) left = 8;

                    panel.css({
                        top: rect.bottom + 4,
                        left: left
                    });
                    panel.addClass('open');
                }
            });

            // Prevent clicks inside panel from bubbling to document
            $(document).on('click', '.col-filter-panel', function(e) {
                e.stopPropagation();
            });

            // Close on outside click or scroll
            $(document).on('click', function() {
                $('.col-filter-panel').removeClass('open');
            });
            $(window).on('scroll resize', function() {
                $('.col-filter-panel').removeClass('open');
            });

            // Reset
            $(document).on('click', '.cfp-reset', function() {
                let col = $(this).data('col');
                let panel = $('[data-col-panel="' + col + '"]');
                panel.find('input[type="checkbox"]').prop('checked', false);
                panel.find('.cfp-input').val('');
                panel.removeClass('open');
                $('[data-col="' + col + '"].col-filter-btn').removeClass('active');
                table.column(col).search('').draw();
            });

            // Apply / Filter
            $(document).on('click', '.cfp-apply', function() {
                let col = $(this).data('col');
                let panel = $('[data-col-panel="' + col + '"]');
                let textInput = panel.find('.cfp-input');
                let checked = panel.find('input[type="checkbox"]:checked');
                let searchVal = '';

                if (textInput.length) {
                    searchVal = textInput.val().trim();
                } else {
                    searchVal = checked.map(function() {
                        return $(this).val();
                    }).get().join('|');
                }

                panel.removeClass('open');
                $('[data-col="' + col + '"].col-filter-btn').toggleClass('active', searchVal !== '');
                table.column(col).search(searchVal, false, false).draw();
            });

            // Allow Enter key in text inputs to trigger filter
            $(document).on('keydown', '.cfp-input', function(e) {
                if (e.key === 'Enter') {
                    $(this).closest('.col-filter-panel').find('.cfp-apply').trigger('click');
                }
            });

        });

        // ── Delete ────────────────────────────────
        function deleteUser(id) {
            let url = "{{ route('users.destroy', ':id') }}".replace(':id', id);
            Swal.fire({
                title: 'Delete this user?',
                html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash"></i></div>This action cannot be undone.',
                width: '380px',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal-theme'
                },
                reverseButtons: true,
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success(res) {
                        if (res.status === 'error') {
                            Swal.fire('Permission Denied', res.message, 'error');
                            return;
                        }
                        showToast(res.message, 'success');
                        $('#users-table').DataTable().ajax.reload(null, false);
                    },
                    error(xhr) {
                        let msg = xhr.responseJSON?.message ?? 'Something went wrong.';
                        Swal.fire('Error!', msg, 'error');
                    }
                });
            });
        }

        // ── Restore ───────────────────────────────
        function restoreUser(id) {
            let url = "{{ route('users.restore', ':id') }}".replace(':id', id);
            Swal.fire({
                title: 'Restore this user?',
                html: '<div class="swal-theme-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-rotate-left"></i></div>The account will become active again.',
                width: '380px',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Yes, restore!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal-theme'
                },
                reverseButtons: true,
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success(res) {
                        if (res.status === 'error') {
                            Swal.fire('Permission Denied', res.message, 'error');
                            return;
                        }
                        showToast(res.message, 'success');
                        $('#users-table').DataTable().ajax.reload(null, false);
                    },
                    error(xhr) {
                        let msg = xhr.responseJSON?.message ?? 'Something went wrong.';
                        Swal.fire('Error!', msg, 'error');
                    }
                });
            });
        }

        // ── Unlock ────────────────────────────────
        function unlockUser(id) {
            let url = "{{ route('users.unlock', ':id') }}".replace(':id', id);
            Swal.fire({
                title: 'Unlock this account?',
                html: '<div class="swal-theme-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-lock-open"></i></div>The user will regain access immediately.',
                width: '380px',
                showCancelButton: true,
                confirmButtonColor: '#16a34a',
                confirmButtonText: 'Yes, unlock!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal-theme'
                },
                reverseButtons: true,
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success(res) {
                        if (res.status) {
                            showToast(res.message, 'success');
                            $('#users-table').DataTable().ajax.reload(null, false);
                        } else {
                            showToast(res.message, 'error');
                        }
                    },
                    error() {
                        Swal.fire('Error!', 'Failed to unlock user.', 'error');
                    }
                });
            });
        }

        // ── Modal helpers ─────────────────────────
        function getUserModal() {
            return bootstrap.Modal.getOrCreateInstance(document.getElementById('userModal'), {
                backdrop: 'static',
                keyboard: false
            });
        }

        function openModal(url, requireManage = true) {
            let canView = @json(auth()->user()->can('view users'));
            let canManage = @json(auth()->user()->can('manage users'));
            let allowed = requireManage ? canManage : canView;

            $('#modalContent').html(`
        <div class="usr-modal-loading">
            <div class="usr-spinner"></div><p>Loading…</p>
        </div>`);
            getUserModal().show();

            if (!allowed) {
                $('#modalContent').html(`
            <div class="usr-modal-loading">
                <i class="fa fa-lock fa-2x mb-3" style="color:#c0272d;"></i>
                <p style="color:#253447;font-weight:600;">You don't have permission for this action.</p>
            </div>`);
                return;
            }
            $.get(url)
                .done(data => $('#modalContent').html(data))
                .fail(() => $('#modalContent').html(`
                    <div class="usr-modal-loading">
                        <i class="fa fa-circle-exclamation fa-2x mb-3" style="color:#c0272d;"></i>
                        <p style="color:#253447;font-weight:600;">Failed to load user details.</p>
                    </div>`));
        }

        function closeModal() {
            getUserModal().hide();
            $('#modalContent').html('');
        }

        function previewAvatar(event, targetId = 'avatarPreview', fallbackId = null) {
            const img = document.getElementById(targetId);
            if (!img || !event.target.files[0]) return;
            const reader = new FileReader();
            reader.onload = () => {
                img.src = reader.result;
                img.style.display = 'block';
                if (fallbackId) {
                    const fallback = document.getElementById(fallbackId);
                    if (fallback) fallback.style.display = 'none';
                }
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        function submitForm() {
            let form = $('#userForm')[0];
            let formData = new FormData(form);
            let saveBtn = $('.ue-btn-save');
            let originalHtml = saveBtn.html();

            // Minimum time the loading spinner stays visible, even if the
            // server responds instantly — so the state is actually seen.
            const MIN_SAVE_SPINNER_MS = 3000;

            $('.ue-input-err').removeClass('ue-input-err');
            $('.ue-err').remove();

            saveBtn.prop('disabled', true).html('<span class="ue-btn-spinner"></span>Saving…');

            let startedAt = Date.now();

            function afterMinDelay(callback) {
                let remaining = MIN_SAVE_SPINNER_MS - (Date.now() - startedAt);
                setTimeout(callback, remaining > 0 ? remaining : 0);
            }

            $.ajax({
                url: form.action,
                type: form.method,
                data: formData,
                processData: false,
                contentType: false,
                success(res) {
                    afterMinDelay(function() {
                        if (res.status) {
                            showToast(res.message, res.status);
                            getUserModal().hide();
                            $('#modalContent').html('');
                            $('#users-table').DataTable().ajax.reload(null, false);
                        } else {
                            saveBtn.prop('disabled', false).html(originalHtml);
                        }
                    });
                },
                error(xhr) {
                    afterMinDelay(function() {
                        saveBtn.prop('disabled', false).html(originalHtml);
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, (key, value) => {
                                let input = $(`[name="${key}"]`);
                                input.addClass('ue-input-err');
                                input.closest('.ue-input-wrap').after(`<span class="ue-err">${value[0]}</span>`);
                            });
                        } else {
                            showToast('Something went wrong', 'danger');
                        }
                    });
                }
            });
        }
    </script>
@endpush
