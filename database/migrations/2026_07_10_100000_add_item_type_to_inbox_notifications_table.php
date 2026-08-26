<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('inbox_notifications', 'item_type')) {
            Schema::table('inbox_notifications', function (Blueprint $table) {
                $table->string('item_type', 20)->default('file')->after('item_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('inbox_notifications', 'item_type')) {
            Schema::table('inbox_notifications', function (Blueprint $table) {
                $table->dropColumn('item_type');
            });
        }
    }
};
