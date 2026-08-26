@extends('admin.layouts.app')
@section('title', 'Roles & Permissions')
@section('page_title', 'Roles & Permissions')

@push('addOnCss')
    <style>
        /* Default role badge, shown after the role name */
        .rol-default-badge {
            display: inline-block;
            margin-left: 8px;
            padding: 2px 9px;
            border-radius: 99px;
            background: #e6f4ea;
            color: #1f8a5f;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .2px;
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid fb-browser-page">
        <div class="fb-browser-card rol-card">

            {{-- ── Card header ── --}}
            <div class="rol-card-header">
                <div class="rol-card-header-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="flex-grow-1">
                    <span style="color:#fff;font-size:15px;font-weight:700;">Roles &amp; Permissions</span>
                </div>
                <div class="rol-header-actions">
                    <div class="rol-search-wrap">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input id="roleSearch" type="search" placeholder="Search roles…">
                    </div>
                    @can('manage users_roles')
                        <button type="button" class="rol-add-btn" id="showAddRoleForm">
                            <i class="fa-solid fa-plus"></i> Add Role
                        </button>
                    @endcan
                </div>
            </div>

            {{-- ── Stats strip ── --}}
            {{-- <div class="rol-stats-strip">
                <div class="rol-stat-item">
                    <span class="rol-stat-dot" style="background:#c0272d;"></span>
                    <span class="rol-stat-val">{{ $roles->count() }}</span>
                    <span class="rol-stat-label">Total Roles</span>
                </div>
                <div class="rol-stat-divider"></div>
                <div class="rol-stat-item">
                    <span class="rol-stat-dot" style="background:#253447;"></span>
                    <span class="rol-stat-label">Click <i class="fa fa-folder fa-xs mx-1" style="color:#1e40af;"></i> to
                        manage folder access inline</span>
                </div>
                <div class="rol-stat-divider"></div>
                <div class="rol-stat-item">
                    <span class="rol-stat-dot" style="background:#065f46;"></span>
                    <span class="rol-stat-label">Click <i class="fa fa-key fa-xs mx-1" style="color:#065f46;"></i> to edit
                        permissions</span>
                </div>
                <div class="rol-stat-divider"></div>
                <div class="rol-stat-item">
                    <span class="rol-stat-dot" style="background:#1e40af;"></span>
                    <span class="rol-stat-label">Click <i class="fa fa-lock fa-xs mx-1" style="color:#1e40af;"></i> to
                        Access the Shared folder</span>
                </div>
            </div> --}}

            {{-- ── Inline Add Role form ── --}}
            @can('manage users_roles')
                <div class="rol-add-form-wrap" id="addRoleFormWrap">
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        <div class="rol-add-form-inner">
                            <input type="text" name="name" placeholder="Enter role name…" autocomplete="off"
                                value="{{ old('name') }}" required>
                            <button type="submit" class="rol-save-btn">
                                <i class="fa fa-plus"></i> Save Role
                            </button>
                            <button type="button" class="rol-cancel-btn" id="cancelAddRole">Cancel</button>
                        </div>
                        @error('name')
                            <div class="text-danger mt-2" style="font-size:12px;">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            @endcan

            {{-- ── Table section ── --}}
            <section class="fb-main rol-table-section">
                <div class="table-responsive">
                    <table class="table rol-table align-middle">
                        <thead>
                            <tr>
                                <th style="white-space:nowrap;">#</th>
                                <th>Role Name</th>
                                @can('manage roles_permissions')
                                    <th>App Permissions</th>
                                @endcan
                                @can('manage users_roles')
                                    <th style="white-space:nowrap;">Default Role</th>
                                @endcan
                                @can('manage users_roles')
                                    <th style="white-space:nowrap;">Actions</th>
                                @endcan
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $key => $role)
                                <tr class="role-row" data-role-name="{{ strtolower($role->name) }}"
                                    data-role-id="{{ $role->id }}">

                                    <td>
                                        <span
                                            style="font-size:11px;color:#94a3b8;font-weight:600;">{{ $key + 1 }}</span>
                                    </td>

                                    <td>
                                        {{ strtr(Str::title(str_replace('-', ' ', $role->name)), ['Devops' => 'DevOps', 'Qa' => 'QA', 'Ux' => 'UX']) }}
                                        @if ($role->is_default)
                                            <span class="rol-default-badge" data-default-badge="{{ $role->id }}">Default
                                                Role</span>
                                        @endif
                                    </td>

                                    @can('manage roles_permissions')
                                        <td>
                                            <a href="{{ route('roles.permissions', $role->id) }}" class="rol-btn rol-btn-perm"
                                                title="Edit Permissions"
                                                style="width:auto;padding:6px 12px;gap:6px;font-size:12px;font-weight:600;">
                                                <i class="fa fa-key"></i>
                                                <span>Permissions</span>
                                            </a>
                                        </td>
                                    @endcan

                                    @can('manage users_roles')
                                        <td>
                                            @if (!in_array($role->name, ['super-admin', 'admin']))
                                                <label class="pm-toggle-wrap">
                                                    <input type="checkbox" class="pm-toggle-input role-default-toggle"
                                                        data-role-id="{{ $role->id }}"
                                                        {{ $role->is_default ? 'checked' : '' }}>
                                                    <span class="pm-toggle-track">
                                                        <span class="pm-toggle-thumb"></span>
                                                    </span>
                                                </label>
                                            @else
                                                <span style="font-size:12px;color:#94a3b8;">—</span>
                                            @endif
                                        </td>
                                    @endcan

                                    @can('manage users_roles')
                                        <td>
                                            <div class="rol-action-group">

                                                {{-- Edit --}}
                                                <button class="rol-btn rol-btn-edit" data-bs-toggle="modal"
                                                    data-bs-target="#editRole{{ $role->id }}" title="Edit Role Name">
                                                    <i class="fa fa-pen"></i>
                                                </button>

                                                {{-- Delete --}}
                                                @if ($role->name !== 'super-admin')
                                                    <button class="rol-btn rol-btn-delete"
                                                        onclick="deleteRole({{ $role->id }})" title="Delete Role">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                @endif

                                            </div>
                                        </td>
                                    @endcan

                                </tr>

                                {{-- Edit Role Modal --}}
                                <div class="modal fade rol-modal" id="editRole{{ $role->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">
                                                        <i class="fa fa-pen me-2"></i>Edit Role
                                                    </h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="form-label"
                                                        style="font-size:12px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px;">
                                                        Role Name
                                                    </label>
                                                    <input type="text" class="form-control" name="name"
                                                        value="{{ $role->name }}"
                                                        style="border-radius:9px;border-color:#dbe4f0;font-size:13px;">
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

                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="rol-empty">
                                            <i class="fa fa-shield-halved"></i>
                                            <p>No roles found.</p>
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
@endsection

@push('script')
    <script>
        /* ── Add Role form toggle ── */
        document.addEventListener('DOMContentLoaded', function() {
            const showBtn = document.getElementById('showAddRoleForm');
            const formWrap = document.getElementById('addRoleFormWrap');
            const cancelBtn = document.getElementById('cancelAddRole');

            if (showBtn && formWrap) {
                @if ($errors->has('name'))
                    formWrap.classList.add('show');
                @endif

                showBtn.addEventListener('click', function() {
                    formWrap.classList.toggle('show');
                    if (formWrap.classList.contains('show')) {
                        formWrap.querySelector('input[name="name"]').focus();
                    }
                });
            }

            if (cancelBtn && formWrap) {
                cancelBtn.addEventListener('click', function() {
                    formWrap.classList.remove('show');
                });
            }

            /* ── Search filter ── */
            const searchInput = document.getElementById('roleSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const q = this.value.trim().toLowerCase();
                    document.querySelectorAll('.role-row').forEach(row => {
                        const name = row.dataset.roleName || '';
                        const show = name.includes(q);
                        row.style.display = show ? '' : 'none';
                    });
                });
            }
        });

        /* ── Default role toggle ── */
        document.addEventListener('change', function(e) {
            if (!e.target.classList.contains('role-default-toggle')) return;

            const checkbox = e.target;
            const roleId = checkbox.dataset.roleId;
            const isDefault = checkbox.checked;
            const url = "{{ route('roles.setDefault', ':id') }}".replace(':id', roleId);

            const formData = new FormData();
            formData.append('is_default', isDefault ? 1 : 0);
            formData.append('_token', '{{ csrf_token() }}');

            fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(res => {
                    if (res.status) {
                        if (typeof showToast === 'function') showToast(res.message, 'success');
                        location.reload();
                    } else {
                        checkbox.checked = !isDefault;
                        if (typeof showToast === 'function') showToast(res.message || 'Error', 'danger');
                        else Swal.fire('Error!', res.message || 'Error', 'error');
                    }
                })
                .catch(() => {
                    checkbox.checked = !isDefault;
                    if (typeof showToast === 'function') showToast('Something went wrong', 'danger');
                    else Swal.fire('Error!', 'Something went wrong', 'error');
                });
        });

        /* ── Delete role ── */
        function deleteRole(id) {
            const deleteUrl = "{{ route('roles.destroy', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Delete this role?',
                html: '<div class="swal-theme-icon" style="background:#fee2e2;color:#dc2626;"><i class="fa-solid fa-trash"></i></div>This role will be permanently deleted.',
                width: '380px',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal-theme'
                },
                reverseButtons: true,
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
                            Swal.fire('Deleted!', res.message, 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            Swal.fire('Error!', res.message || 'Error', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error!', 'Something went wrong', 'error'));
            });
        }
    </script>
@endpush
