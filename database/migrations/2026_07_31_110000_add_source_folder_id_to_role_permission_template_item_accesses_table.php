<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('role_permission_template_item_accesses', 'source_folder_id')) {
            Schema::table('role_permission_template_item_accesses', function (Blueprint $table) {
                // 0 = override targets the top-level template item itself.
                // Any other value = a specific nested descendant (by its real
                // master-folder id) under that item's preview sub-tree — lets an
                // admin set a different permission per nested folder before any
                // of it is actually created.
                $table->unsignedBigInteger('source_folder_id')->default(0)->after('permission_template_item_id');
            });
        }

        // The old 2-column unique index also backs the role_id FK — give the
        // FK its own plain index first so the old unique can be dropped
        // safely, then replace it with the real 3-column constraint.
        Schema::table('role_permission_template_item_accesses', function (Blueprint $table) {
            $table->index('role_id', 'role_tpl_item_role_id_index');
        });

        Schema::table('role_permission_template_item_accesses', function (Blueprint $table) {
            $table->dropUnique('role_tpl_item_access_unique');
            $table->unique(['role_id', 'permission_template_item_id', 'source_folder_id'], 'role_tpl_item_source_unique');
        });
    }

    public function down(): void
    {
        Schema::table('role_permission_template_item_accesses', function (Blueprint $table) {
            $table->dropUnique('role_tpl_item_source_unique');
            $table->unique(['role_id', 'permission_template_item_id'], 'role_tpl_item_access_unique');
            $table->dropIndex('role_tpl_item_role_id_index');
            $table->dropColumn('source_folder_id');
        });
    }
};
