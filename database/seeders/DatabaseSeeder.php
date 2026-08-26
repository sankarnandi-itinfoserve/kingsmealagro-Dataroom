<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            PermissionSeeder::class,
        ]);
    }
    
    // public function run(): void
    // {
    //     User::updateOrCreate(
    //         ['email' => 'md.arif@weavers-web.com'],
    //         [
               
    //             'email' => 'md.arif@weavers-web.com',
    //             'password' => Hash::make('123456789'),

    //             // REQUIRED FIELDS
    //             'emp_id' => 'EMP001',
    //             'username' => 'admin',
    //             'replay_email' => 'md.arif@weavers-web.com',
    //             'role' => 'admin',
    //             'azure_id' => 'azure-admin-001',
    //             'displayName' => 'Admin User',

    //             // OPTIONAL BUT GOOD TO FILL
    //             'fname' => 'Admin',
    //             'lname' => 'User',
    //             'initials' => 'AU',
    //             'user_type' => 'internal',
    //             'job_title' => 'Administrator',
    //             'active' => true,
    //             'timezone' => 'Asia/Kolkata',
    //             'locale' => 'EN',
    //         ]
    //     );
    // }
}