@extends('admin.layouts.app')
@section('title', 'Manage Permissions')
@section('page_title', 'Manage Permissions')

@section('content')

    <div class="container-fluid fb-browser-page">
        <form id="permissionForm" action="{{ route('roles.permissions.update', $role->id) }}" method="POST">
            @csrf

            {{-- ── PAGE CARD ── --}}
            <div class="fb-browser-card">

                {{-- Header --}}
                {{-- <div class="fb-header-row">
                    <div>
                        <nav class="fb-breadcrumb" aria-label="Breadcrumb">
                            <a href="{{ route('roles.index') }}" class="fb-crumb">
                                <i class="fa-solid fa-user-shield me-1"></i>Roles
                            </a>
                            <span class="fb-crumb-sep">/</span>
                            <span class="fb-crumb-current">Permissions</span>
                        </nav>
                        <div class="pm-role-title-row">
                            <span class="pm-role-chip">
                                <i class="fa-solid fa-key me-1"></i>
                                {{ ucfirst(str_replace('-', ' ', $role->name)) }}
                            </span>
                            <span class="pm-role-sub">Configure what this role is allowed to do across the system.</span>
                        </div>
                    </div>
                    <div class="fb-header-actions">
                        <a href="{{ route('roles.index') }}" class="pm-back-btn">
                            <i class="fa-solid fa-arrow-left me-1"></i>Back
                        </a>
                        <button type="submit" class="pm-save-btn">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Update Permissions
                        </button>
                    </div>
                </div> --}}

                {{-- Select-All bar --}}
                <div class="pm-select-all-bar">
                    <label class="pm-select-all-label" for="checkAll">
                        <span class="pm-toggle-wrap">
                            <input type="checkbox" id="checkAll" class="pm-toggle-input">
                            <span class="pm-toggle-track">
                                <span class="pm-toggle-thumb"></span>
                            </span>
                        </span>
                        <span class="pm-select-all-text">Select All Permissions</span>
                    </label>
                    <span class="pm-perm-counter">
                        <span id="checkedCount">0</span> / <span id="totalCount">0</span> selected
                    </span>
                </div>

                {{-- All permission cards — 3 per row --}}
                @php
                    $moduleOrder = ['dashboard', 'project_folders', 'favorite_folders', 'inbox', 'projects_management', 'analytics', 'archives', 'users', 'users_roles', 'roles_permissions', 'all_templates', 'master_folders', 'companies'];
                    $sortedPermissions = collect($moduleOrder)
                        ->filter(fn($m) => isset($permissions[$m]))
                        ->mapWithKeys(fn($m) => [$m => $permissions[$m]]);
                @endphp
                <div class="row g-3">
                    @foreach ($sortedPermissions as $module => $permissionGroup)
                        <div class="col-md-4">
                            @include('admin.roles._perm_card', [
                                'module' => $module,
                                'permissionGroup' => $permissionGroup,
                                'role' => $role,
                            ])
                        </div>
                    @endforeach
                </div>

            </div>{{-- /.fb-browser-card --}}

            {{-- Sticky save footer --}}
            <div class="pm-sticky-footer">
                <span class="pm-footer-info">
                    <i class="fa-solid fa-circle-info me-1" style="color:#253447;"></i>
                    Changes apply immediately after saving.
                </span>
                <div class="d-flex gap-2">
                    <a href="{{ route('roles.index') }}" class="pm-back-btn">
                        <i class="fa-solid fa-arrow-left me-1"></i>Back
                    </a>
                    <button type="submit" class="pm-save-btn">
                        <i class="fa-solid fa-floppy-disk me-2"></i>Update Permissions
                    </button>
                </div>
            </div>

        </form>
    </div>

@endsection


@push('addOnCss')
    <style>


    </style>
@endpush


@push('script')
    <script>
        $(function() {

            // ── Counter update ──────────────────────────
            function updateCounter() {
                const total = $('.permission-checkbox').length;
                const checked = $('.permission-checkbox:checked').length;
                $('#totalCount').text(total);
                $('#checkedCount').text(checked);
                $('#checkAll').prop('checked', total > 0 && total === checked);
                // sync visual toggle
                const track = $('#checkAll').closest('.pm-toggle-wrap').find('.pm-toggle-track');
            }

            // ── Select All toggle ───────────────────────
            $('#checkAll').on('change', function() {
                $('.permission-checkbox:not(:disabled)').prop('checked', $(this).prop('checked'));
                updateCounter();
            });

            // ── Individual checkbox ─────────────────────
            $(document).on('change', '.permission-checkbox', function() {
                const label = $(this).siblings('.pm-check-label').text().toLowerCase();
                const checked = $(this).prop('checked');

                // Manage checked → auto-check view above it
                if (label.includes('manage') && checked) {
                    $(this).closest('.pm-check-item').prevAll('.pm-check-item').first()
                        .find('.pm-checkbox').prop('checked', true);
                }
                // View unchecked → auto-uncheck manage below it
                if (label.includes('view') && !checked) {
                    $(this).closest('.pm-check-item').nextAll('.pm-check-item').first()
                        .find('.pm-checkbox').prop('checked', false);
                }

                updateCounter();
            });

            updateCounter();
        });
    </script>
@endpush
