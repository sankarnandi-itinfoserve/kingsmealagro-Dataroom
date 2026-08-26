@extends('admin.layouts.app')
@section('title', 'Groups')
@section('page_title', 'Groups')

@push('addOnCss')
    <style>
        /* ── Column sort / filter ──────────────────────────────────────── */
        .col-has-filter {
            position: relative;
        }

        .col-th-inner {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 4px;
            white-space: nowrap;
        }

        .sortable {
            cursor: pointer;
            user-select: none;
        }

        .sortable i {
            margin-left: 6px;
            color: #94a3b8;
        }

        .group-status-badge {
            display: inline-flex;
            align-items: center;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .group-status-active {
            background: #16a34a;
            color: #fff;
        }

        .group-status-inactive {
            background: #dc2626;
            color: #fff;
        }

        .group-members-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 26px;
            padding: 3px 8px;
            border-radius: 20px;
            background: #f1f5f9;
            color: #475569;
            font-size: 11.5px;
            font-weight: 700;
        }

        /* ── User-picker inside the modal ──────────────────────────────── */
        .grp-user-picker {
            border: 1.5px solid #dbe4f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .grp-user-search {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-bottom: 1px solid #eef2f7;
            background: #f8fafc;
        }

        .grp-user-search i {
            color: #94a3b8;
            font-size: 12px;
        }

        .grp-user-search input {
            border: none;
            outline: none;
            background: transparent;
            font-size: 13px;
            width: 100%;
        }

        .grp-user-list {
            max-height: 260px;
            overflow-y: auto;
            padding: 6px;
        }

        .grp-user-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 8px;
            border-radius: 8px;
            cursor: pointer;
            transition: background .12s;
        }

        .grp-user-row:hover {
            background: #f1f5f9;
        }

        .grp-user-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #253447;
            cursor: pointer;
            flex-shrink: 0;
        }

        .grp-user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            background: #eef2f7;
        }

        .grp-user-icon-default {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef2f7;
            color: #94a3b8;
            font-size: 14px;
            flex-shrink: 0;
        }

        .grp-user-name {
            font-size: 13px;
            color: #1e293b;
            font-weight: 600;
            line-height: 1.2;
        }

        .grp-user-email {
            font-size: 11px;
            color: #94a3b8;
        }

        .grp-user-count-chip {
            margin-left: auto;
            font-size: 11px;
            font-weight: 700;
            color: #253447;
        }

        .grp-modal-label {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .5px;
            display: block;
            margin-bottom: 6px;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card rol-card">

            {{-- ── Card header ── --}}
            <div class="rol-card-header">
                <div class="rol-card-header-icon">
                    <i class="fa-solid fa-people-group"></i>
                </div>
                <div class="flex-grow-1">
                    <span style="color:#fff;font-size:15px;font-weight:700;">Groups</span>
                </div>
                <div class="rol-header-actions">
                    <div class="rol-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input id="groupSearch" type="search" placeholder="Search groups…">
                    </div>
                    @can('manage groups')
                        <button type="button" class="rol-add-btn" data-bs-toggle="modal" data-bs-target="#addGroupModal">
                            <i class="fa-solid fa-plus"></i> Add Group
                        </button>
                    @endcan
                </div>
            </div>

            {{-- ── Table section ── --}}
            <section class="fb-main rol-table-section">
                <div class="table-responsive">
                    <table class="table rol-table align-middle" id="groups-table">
                        <thead>
                            <tr>
                                <th style="white-space:nowrap;">#</th>
                                <th class="sortable" data-sort="name">
                                    <div class="col-th-inner">
                                        <span>Group Name <i class="fa-solid fa-sort sort-icon"></i></span>
                                    </div>
                                </th>
                                <th class="sortable" data-sort="members" style="white-space:nowrap;">
                                    <div class="col-th-inner">
                                        <span>Members <i class="fa-solid fa-sort sort-icon"></i></span>
                                    </div>
                                </th>
                                <th style="white-space:nowrap;">Created By</th>
                                <th class="sortable" data-sort="created">
                                    <div class="col-th-inner">
                                        <span>Created At <i class="fa-solid fa-sort sort-icon"></i></span>
                                    </div>
                                </th>
                                <th style="white-space:nowrap;">Status</th>
                                @can('manage groups')
                                    <th style="white-space:nowrap;">Actions</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody id="groupsTableBody">
                            @forelse($groups as $key => $group)
                                <tr class="role-row" data-name="{{ strtolower($group->name) }}"
                                    data-created="{{ $group->created_at->timestamp }}"
                                    data-members="{{ $group->users_count }}">

                                    <td>
                                        <span style="font-size:11px;color:#94a3b8;font-weight:600;">{{ $key + 1 }}</span>
                                    </td>

                                    <td>{{ $group->name }}</td>

                                    <td>
                                        <span class="group-members-badge">{{ $group->users_count }}</span>
                                    </td>

                                    <td>
                                        <span style="font-size:12px;color:#64748b;">
                                            {{ $group->creator->full_name ?? '—' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span style="font-size:12px;color:#64748b;">
                                            {{ $group->created_at->format('d M Y') }}
                                        </span>
                                    </td>

                                    <td>
                                        @if ($group->deleted_at)
                                            <span class="group-status-badge group-status-inactive">
                                                <i class="fa-solid fa-circle-minus me-1"></i>Inactive
                                            </span>
                                        @else
                                            <span class="group-status-badge group-status-active">
                                                <i class="fa-solid fa-circle-check me-1"></i>Active
                                            </span>
                                        @endif
                                    </td>

                                    @can('manage groups')
                                        <td>
                                            <div class="rol-action-group">
                                                @if ($group->deleted_at)
                                                    <button class="rol-btn rol-btn-edit"
                                                        onclick="restoreGroup({{ $group->id }})" title="Restore Group">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </button>
                                                @else
                                                    <a href="{{ route('folder-access.edit', ['type' => 'group', 'id' => $group->id]) }}"
                                                        class="rol-btn rol-btn-edit" title="Folder Access">
                                                        <i class="fa-solid fa-folder-open"></i>
                                                    </a>

                                                    <button class="rol-btn rol-btn-edit" data-bs-toggle="modal"
                                                        data-bs-target="#editGroup{{ $group->id }}" title="Edit Group">
                                                        <i class="fa fa-pen"></i>
                                                    </button>

                                                    <button class="rol-btn rol-btn-delete"
                                                        onclick="deleteGroup({{ $group->id }})" title="Delete Group">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    @endcan

                                </tr>

                                {{-- Edit Group Modal --}}
                                @can('manage groups')
                                    @php $groupUserIds = $group->users->pluck('id')->all(); @endphp
                                    <div class="modal fade rol-modal" id="editGroup{{ $group->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('groups.update', $group->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <i class="fa fa-pen me-2"></i>Edit Group
                                                        </h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <label class="grp-modal-label">Group Name</label>
                                                        <input type="text" class="form-control" name="name"
                                                            value="{{ $group->name }}"
                                                            style="border-radius:9px;border-color:#dbe4f0;font-size:13px;margin-bottom:14px;"
                                                            required>

                                                        <label class="grp-modal-label">Members</label>
                                                        <div class="grp-user-picker">
                                                            <div class="grp-user-search">
                                                                <i class="fa-solid fa-magnifying-glass"></i>
                                                                <input type="text" class="grp-user-filter"
                                                                    placeholder="Search users…">
                                                            </div>
                                                            <div class="grp-user-list">
                                                                @foreach ($users as $u)
                                                                    <label class="grp-user-row"
                                                                        data-uname="{{ strtolower($u->full_name . ' ' . $u->email) }}">
                                                                        <input type="checkbox" name="user_ids[]"
                                                                            value="{{ $u->id }}"
                                                                            {{ in_array($u->id, $groupUserIds) ? 'checked' : '' }}>
                                                                        @if ($u->avatar)
                                                                            <img src="{{ asset('storage/' . $u->avatar) }}"
                                                                                class="grp-user-avatar" alt="">
                                                                        @else
                                                                            <i class="fa-solid fa-circle-user grp-user-icon-default"></i>
                                                                        @endif
                                                                        <div>
                                                                            <div class="grp-user-name">{{ $u->full_name }}</div>
                                                                            <div class="grp-user-email">{{ $u->email }}</div>
                                                                        </div>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="rol-cancel-btn"
                                                            data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="rol-modal-save">
                                                            <i class="fa fa-check me-1"></i> Update
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endcan

                            @empty
                                <tr>
                                    <td colspan="7">
                                        <div class="rol-empty">
                                            <i class="fa-solid fa-people-group"></i>
                                            <p>No groups found.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    {{-- ── Add Group Modal ── --}}
    @can('manage groups')
        <div class="modal fade rol-modal" id="addGroupModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form action="{{ route('groups.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fa fa-plus me-2"></i>Add Group</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="grp-modal-label">Group Name</label>
                            <input type="text" class="form-control" name="name" placeholder="Enter group name…"
                                value="{{ old('name') }}"
                                style="border-radius:9px;border-color:#dbe4f0;font-size:13px;margin-bottom:14px;"
                                required>

                            <label class="grp-modal-label">Members</label>
                            <div class="grp-user-picker">
                                <div class="grp-user-search">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                    <input type="text" class="grp-user-filter" placeholder="Search users…">
                                </div>
                                <div class="grp-user-list">
                                    @foreach ($users as $u)
                                        <label class="grp-user-row"
                                            data-uname="{{ strtolower($u->full_name . ' ' . $u->email) }}">
                                            <input type="checkbox" name="user_ids[]" value="{{ $u->id }}">
                                            @if ($u->avatar)
                                                <img src="{{ asset('storage/' . $u->avatar) }}" class="grp-user-avatar"
                                                    alt="">
                                            @else
                                                <i class="fa-solid fa-circle-user grp-user-icon-default"></i>
                                            @endif
                                            <div>
                                                <div class="grp-user-name">{{ $u->full_name }}</div>
                                                <div class="grp-user-email">{{ $u->email }}</div>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="rol-cancel-btn" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="rol-modal-save">
                                <i class="fa fa-check me-1"></i> Save Group
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ── Search-filter the user checkbox list inside each modal ──
            document.querySelectorAll('.grp-user-filter').forEach(function(input) {
                input.addEventListener('input', function() {
                    const q = this.value.trim().toLowerCase();
                    const list = this.closest('.grp-user-picker').querySelectorAll('.grp-user-row');
                    list.forEach(row => {
                        row.style.display = !q || (row.dataset.uname || '').includes(q) ? 'flex' : 'none';
                    });
                });
            });

            @if ($errors->any())
                var addModalEl = document.getElementById('addGroupModal');
                if (addModalEl) {
                    new bootstrap.Modal(addModalEl).show();
                }
            @endif

            // ── Global search ──
            function applyFilters() {
                const searchQ = (document.getElementById('groupSearch')?.value || '').trim().toLowerCase();
                document.querySelectorAll('#groupsTableBody .role-row').forEach(row => {
                    const matches = !searchQ || (row.dataset.name || '').includes(searchQ);
                    row.style.display = matches ? '' : 'none';
                });
            }

            const searchInput = document.getElementById('groupSearch');
            if (searchInput) {
                searchInput.addEventListener('input', applyFilters);
            }

            // ── Column sort (Group Name, Members, Created At) ──
            const groupsTbody = document.getElementById('groupsTableBody');
            const originalRowOrder = [...groupsTbody.querySelectorAll('tr.role-row')];

            let sortKey = '',
                sortDir = null;

            document.querySelectorAll('.sortable').forEach(function(th) {
                th.addEventListener('click', function() {
                    const key = this.dataset.sort;

                    if (sortKey !== key) {
                        sortDir = 'asc';
                    } else if (sortDir === 'asc') {
                        sortDir = 'desc';
                    } else if (sortDir === 'desc') {
                        sortDir = null;
                    } else {
                        sortDir = 'asc';
                    }
                    sortKey = sortDir ? key : '';

                    document.querySelectorAll('.sortable .sort-icon').forEach(i => i.className =
                        'fa-solid fa-sort sort-icon');

                    if (!sortDir) {
                        originalRowOrder.forEach(row => groupsTbody.appendChild(row));
                        return;
                    }

                    const rows = [...groupsTbody.querySelectorAll('tr.role-row')];

                    rows.sort(function(a, b) {
                        if (key === 'created' || key === 'members') {
                            const av = parseInt(a.dataset[key]) || 0;
                            const bv = parseInt(b.dataset[key]) || 0;
                            return sortDir === 'asc' ? av - bv : bv - av;
                        }
                        const av = (a.dataset[key] || '').toString();
                        const bv = (b.dataset[key] || '').toString();
                        return sortDir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
                    });

                    rows.forEach(row => groupsTbody.appendChild(row));

                    this.querySelector('.sort-icon').className = 'fa-solid fa-sort-' + (sortDir === 'asc' ?
                        'up' : 'down') + ' sort-icon';
                });
            });
        });

        function deleteGroup(id) {
            const deleteUrl = "{{ route('groups.destroy', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Delete this group?',
                html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash"></i></div>This group will be moved to trash.',
                width: '380px', showCancelButton: true,
                confirmButtonColor: '#dc2626', confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel', customClass: { popup: 'swal-theme' }, reverseButtons: true,
            }).then(result => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('_method', 'DELETE');
                formData.append('_token', '{{ csrf_token() }}');

                fetch(deleteUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status) {
                            showToast(res.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast(res.message || 'Error', 'danger');
                        }
                    })
                    .catch(() => showToast('Something went wrong', 'danger'));
            });
        }

        function restoreGroup(id) {
            const restoreUrl = "{{ route('groups.restore', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Restore this group?',
                html: '<div class="swal-theme-icon" style="background:#dcfce7;color:#16a34a;"><i class="fa-solid fa-rotate-left"></i></div>The group will become active again.',
                width: '380px', showCancelButton: true,
                confirmButtonColor: '#16a34a', confirmButtonText: 'Yes, restore it!',
                cancelButtonText: 'Cancel', customClass: { popup: 'swal-theme' }, reverseButtons: true,
            }).then(result => {
                if (!result.isConfirmed) return;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                fetch(restoreUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.status) {
                            showToast(res.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            showToast(res.message || 'Error', 'danger');
                        }
                    })
                    .catch(() => showToast('Something went wrong', 'danger'));
            });
        }
    </script>
@endpush
