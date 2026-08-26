<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            ['name' => 'Admin', 'slug' => 'admin'],
            // ['name' => 'Security Reviewer', 'slug' => 'security-reviewer'],
            // ['name' => 'Product Owner', 'slug' => 'product-owner'],
            // ['name' => 'Tech Lead / Architect', 'slug' => 'tech-lead-architect'],
            // ['name' => 'Front-end Engineers', 'slug' => 'frontend-engineers'],
            // ['name' => 'Back-end Engineers', 'slug' => 'backend-engineers'],
            // ['name' => 'DevOps Engineer', 'slug' => 'devops-engineer'],
            // ['name' => 'QA Engineer', 'slug' => 'qa-engineer'],
            // ['name' => 'UX Designer', 'slug' => 'ux-designer'],
            ['name' => 'User', 'slug' => 'user'],
        ];

        foreach ($roles as $role) {
            $created = Role::firstOrCreate(['name' => $role['slug']]);

            if ($role['slug'] === 'user') {
                $created->update(['is_default' => true]);
            }
        }
    }
}
