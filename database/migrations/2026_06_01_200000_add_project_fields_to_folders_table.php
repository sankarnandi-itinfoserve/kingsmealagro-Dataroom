<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            if (!Schema::hasColumn('folders', 'code_name')) {
                $table->string('code_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('folders', 'deal_type')) {
                $table->string('deal_type')->nullable()->after('code_name');
            }
            if (!Schema::hasColumn('folders', 'start_date')) {
                $table->date('start_date')->nullable()->after('deal_type');
            }
            if (!Schema::hasColumn('folders', 'target_close_date')) {
                $table->date('target_close_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('folders', 'status')) {
                $table->enum('status', ['active', 'closed', 'archived'])->nullable()->after('target_close_date');
            }
            if (!Schema::hasColumn('folders', 'project_status')) {
                $table->string('project_status')->default('')->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn(['code_name', 'deal_type', 'start_date', 'target_close_date', 'status', 'project_status']);
        });
    }
};
