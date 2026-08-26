<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('default_folder_access')->default(true)->after('is_default');
        });

        // Force every existing row to true explicitly — matches the
        // behavior already in place before this toggle existed (every
        // non-super-admin role auto-granted Read Only + Download on new/
        // synced content), so shipping this doesn't silently take that
        // away from any role until an admin actually unchecks it.
        DB::table('roles')->update(['default_folder_access' => true]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('default_folder_access');
        });
    }
};
