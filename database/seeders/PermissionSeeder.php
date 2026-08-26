<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [

            'dashboard',
            'project_folders',
            'favorite_folders',
            'projects_management',
            'analytics',
            'archives',
            'users',
            'users_roles',
            'roles_permissions',
            'settings',
            'invite_users',
            'master_folders',
            'groups',

        ];

        foreach ($modules as $module) {

            Permission::firstOrCreate([
                'name' => 'view '.$module,
                'guard_name' => 'web'
            ]);

            Permission::firstOrCreate([
                'name' => 'manage '.$module,
                'guard_name' => 'web'
            ]);
        }


        $superadminRole = Role::find(1);

        if ($superadminRole) {
            $permissions = Permission::pluck('name')->toArray();

            $superadminRole->syncPermissions($permissions);
        }
        // ✅ Give ONLY "view dashboard" to all other roles
        $otherRoles = Role::where('id', '!=', 1)->get();

        foreach ($otherRoles as $role) {
            $role->givePermissionTo('view dashboard');
        }

    }
}
