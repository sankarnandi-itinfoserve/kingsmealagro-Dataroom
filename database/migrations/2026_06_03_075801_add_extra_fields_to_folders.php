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
            $table->string('item_id')->nullable()->after('name');
            $table->string('web_url')->nullable()->after('item_id');
            $table->enum('type', ['folder', 'file'])->nullable()->after('web_url');
            $table->text('response')->nullable()->after('type');
            $table->string('parent_item_id')->nullable()->after('item_id');
            $table->string('drive_id')->nullable()->after('parent_item_id');
            $table->text('download_url')->nullable()->after('response');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('item_id');
            $table->dropColumn('web_url');
            $table->dropColumn('type');
            $table->dropColumn('response');
            $table->dropColumn('parent_item_id');
            $table->dropColumn('drive_id');
            $table->dropColumn('download_url');
        });
    }
};
