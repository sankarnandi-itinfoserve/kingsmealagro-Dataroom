<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('type');
        });

        // Force every existing row explicitly rather than relying on the
        // column DEFAULT alone for pre-existing rows.
        DB::table('folders')->update(['is_template' => false]);
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('is_template');
        });
    }
};
