<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('inbox_notifications', 'item_folder_id')) {
            Schema::table('inbox_notifications', function (Blueprint $table) {
                // The created item's own folder id (distinct from folder_id,
                // which is the *parent* folder it was created inside) — lets
                // us check whether the notified user still has access to the
                // actual new project/subfolder, not just its parent.
                $table->unsignedBigInteger('item_folder_id')->nullable()->after('folder_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inbox_notifications', 'item_folder_id')) {
            Schema::table('inbox_notifications', function (Blueprint $table) {
                $table->dropColumn('item_folder_id');
            });
        }
    }
};
