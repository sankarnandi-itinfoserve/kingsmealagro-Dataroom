<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original create-table migration's auto-generated unique index name
     * (permission_template_items_permission_template_id_folder_name_unique,
     * 70 chars) exceeds MySQL's 64-char identifier limit, so that ALTER TABLE
     * silently failed and the table was left without the constraint. Adding
     * it here under a short, explicit name.
     */
    public function up(): void
    {
        $indexName = 'pti_template_folder_unique';

        if (!Schema::hasTable('permission_template_items')) {
            return;
        }

        $indexes = Schema::getIndexes('permission_template_items');
        $exists = collect($indexes)->contains(fn ($index) => $index['name'] === $indexName);

        if (!$exists) {
            Schema::table('permission_template_items', function (Blueprint $table) use ($indexName) {
                $table->unique(['permission_template_id', 'folder_name'], $indexName);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('permission_template_items')) {
            Schema::table('permission_template_items', function (Blueprint $table) {
                $table->dropUnique('pti_template_folder_unique');
            });
        }
    }
};
