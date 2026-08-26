@php
    $icons = [
        'dashboard'          => 'fa-gauge-high',
        'project_folders' => 'fa-folder-open',
        'favorite_folders'   => 'fa-star',
        'inbox'             => 'fa-inbox',
        'projects_management'          => 'fa-diagram-project',
        'users'             => 'fa-user',
        'users_roles'       => 'fa-user-shield',
        'roles_permissions' => 'fa-key',
        'analytics'         => 'fa-chart-line',
        'archives'          => 'fa-box-archive',
        'all_templates'     => 'fa-layer-group',
        'master_folders'    => 'fa-sitemap',
    ];
    $icon = $icons[$module] ?? 'fa-lock';
    $count = count($permissionGroup);
@endphp

<div class="pm-card">
    <div class="pm-card-header">
        <div class="pm-card-header-icon">
            <i class="fa-solid {{ $icon }}"></i>
        </div>
        <p class="pm-card-title">{{ ucwords(str_replace('_', ' ', $module)) }}</p>
        <span class="pm-card-count">{{ $count }}</span>
    </div>
    <div class="pm-card-body">
        @foreach ($permissionGroup as $permission)
            @php
                $parts  = explode(' ', $permission->name);
                $action = strtolower($parts[0] ?? '');
                $pillClass = match(true) {
                    $action === 'view'   => 'pm-pill-view',
                    $action === 'manage' => 'pm-pill-manage',
                    default              => 'pm-pill-other',
                };
                $isDisabled = (!empty($permission->name) && $permission->name === 'view dashboard')
                           || (!empty($module) && in_array($module, ['your_tickets','your_tasks']));
            @endphp
            <label class="pm-check-item" for="permission{{ $permission->id }}">
                <input
                    type="checkbox"
                    class="pm-checkbox permission-checkbox"
                    id="permission{{ $permission->id }}"
                    name="permissions[]"
                    value="{{ $permission->name }}"
                    data-action="{{ $action }}"
                    data-module="{{ $parts[1] ?? '' }}"
                    @disabled($isDisabled)
                    @if ($role->hasPermissionTo($permission->name)) checked @endif
                >
                <span class="pm-check-label">
                    {{ ucwords(str_replace(['-', '_'], ' ', $permission->name)) }}
                </span>
                <span class="pm-action-pill {{ $pillClass }}">{{ $action }}</span>
            </label>
        @endforeach
    </div>
</div>
