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
            $table->boolean('is_default')->default(false)->after('name');
        });

        // Force every existing row to false explicitly — don't rely on the
        // column DEFAULT alone for pre-existing rows.
        DB::table('roles')->update(['is_default' => false]);
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
