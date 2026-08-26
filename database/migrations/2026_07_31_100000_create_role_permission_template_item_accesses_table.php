<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permission_template_item_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('permission_template_item_id')
                ->constrained('permission_template_items', 'id', 'rpt_item_access_item_id_foreign')
                ->cascadeOnDelete();
            $table->json('folder_permission');
            $table->timestamps();

            $table->unique(['role_id', 'permission_template_item_id'], 'role_tpl_item_access_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permission_template_item_accesses');
    }
};
