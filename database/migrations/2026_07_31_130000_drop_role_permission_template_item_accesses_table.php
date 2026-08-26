<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// The "ghost" (not-yet-created template folder) preview system is replaced
// by materializing real folders the moment a project's template is set
// (see ProjectController::materializeTemplateFolders()) — this table (and
// the per-role permission overrides it stored for folders that didn't
// exist yet) is no longer needed at all.
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('role_permission_template_item_accesses');
    }

    public function down(): void
    {
        Schema::create('role_permission_template_item_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_template_item_id')->constrained('permission_template_items', 'id', 'rpt_item_access_item_id_foreign')->cascadeOnDelete();
            $table->unsignedBigInteger('source_folder_id')->default(0);
            $table->json('folder_permission');
            $table->timestamps();
            $table->unique(['role_id', 'permission_template_item_id', 'source_folder_id'], 'role_tpl_item_source_unique');
        });
    }
};
