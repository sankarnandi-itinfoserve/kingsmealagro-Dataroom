<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'md.arif@weavers-web.com', 'role' => 'super-admin'],
            ['name' => 'Admin', 'email' => 'sankar.nandi@weavers-web.com', 'role' => 'admin'],
            // ['name' => 'Security Reviewer', 'email' => 'security@yopmail.com', 'role' => 'security-reviewer'],
            // ['name' => 'Product Owner', 'email' => 'product@yopmail.com', 'role' => 'product-owner'],
            // ['name' => 'Tech Lead', 'email' => 'techlead@yopmail.com', 'role' => 'tech-lead-architect'],
            // ['name' => 'Frontend Engineer', 'email' => 'frontend@yopmail.com', 'role' => 'frontend-engineers'],
            // ['name' => 'Backend Engineer', 'email' => 'backend@yopmail.com', 'role' => 'backend-engineers'],
            // ['name' => 'DevOps Engineer', 'email' => 'devops@yopmail.com', 'role' => 'devops-engineer'],
            // ['name' => 'QA Engineer', 'email' => 'qa@yopmail.com', 'role' => 'qa-engineer'],
            // ['name' => 'UX Designer', 'email' => 'ux@yopmail.com', 'role' => 'ux-designer'],
            ['name' => 'John Doe', 'email' => 'john.doe@yopmail.com', 'role' => 'user'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@yopmail.com', 'role' => 'user'],
            ['name' => 'Michael Brown', 'email' => 'michael.brown@yopmail.com', 'role' => 'user'],
            ['name' => 'Emily Davis', 'email' => 'emily.davis@yopmail.com', 'role' => 'user'],
            ['name' => 'Chris Wilson', 'email' => 'chris.wilson@yopmail.com', 'role' => 'user'],
        ];
        foreach ($users as $index => $userData) {

            $names = explode(' ', $userData['name']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'fname' => $names[0] ?? '',
                    'lname' => $names[1] ?? '',
                    'displayName' => $userData['name'],
                    'username' => strtolower(str_replace(' ', '_', $userData['name'])),
                    'email' => $userData['email'],
                    'password' => Hash::make('password123'),

                    'role' => $userData['role'],
                    'user_type' => 'user',
                    'active' => 1,

                    'emp_id' => 'EMP' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'replay_email' => $userData['email'],
                ]
            );
            $user->assignRole([$userData['role']]);
        }

    }
}
