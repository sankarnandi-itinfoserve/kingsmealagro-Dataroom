<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('role_folder_accesses')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $perms = json_decode($row->folder_permission, true) ?? [];

                if (in_array('E', $perms, true) || in_array('D', $perms, true)) {
                    $perms = array_values(array_unique(array_merge(
                        array_diff($perms, ['E', 'D']),
                        ['M']
                    )));

                    DB::table('role_folder_accesses')
                        ->where('id', $row->id)
                        ->update(['folder_permission' => json_encode($perms)]);
                }
            }
        });
    }

    public function down(): void
    {
        DB::table('role_folder_accesses')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $perms = json_decode($row->folder_permission, true) ?? [];

                if (in_array('M', $perms, true)) {
                    $perms = array_values(array_unique(array_merge(
                        array_diff($perms, ['M']),
                        ['E', 'D']
                    )));

                    DB::table('role_folder_accesses')
                        ->where('id', $row->id)
                        ->update(['folder_permission' => json_encode($perms)]);
                }
            }
        });
    }
};
