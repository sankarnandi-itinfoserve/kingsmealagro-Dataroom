<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recent_files', function (Blueprint $table) {
            $table->unsignedInteger('view_count')->default(1)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('recent_files', function (Blueprint $table) {
            $table->dropColumn('view_count');
        });
    }
};
