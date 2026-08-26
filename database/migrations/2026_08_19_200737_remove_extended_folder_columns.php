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
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropConstrainedForeignId('template_source_folder_id');
            $table->dropConstrainedForeignId('permission_template_id');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn([
                'parent_id',
                'is_template',
                'response',
                'download_url',
                'code_name',
                'deal_type',
                'start_date',
                'target_close_date',
                'status',
                'project_status',
                'archived_at',
                'retention_days',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_template')->default(false);
            $table->foreignId('permission_template_id')->nullable()
                ->constrained('permission_templates')->nullOnDelete();
            $table->foreignId('template_source_folder_id')->nullable()
                ->constrained('folders', 'id', 'folders_template_source_folder_id_foreign')
                ->nullOnDelete();
            $table->text('response')->nullable();
            $table->text('download_url')->nullable();
            $table->string('code_name')->nullable();
            $table->string('deal_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('target_close_date')->nullable();
            $table->enum('status', ['active', 'closed', 'archived'])->nullable();
            $table->string('project_status')->default('');
            $table->timestamp('archived_at')->nullable();
            $table->unsignedSmallInteger('retention_days')->nullable();
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('folders')->nullOnDelete();
        });
    }
};
