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
            $table->dropColumn([
                'item_id',
                'drive_id',
                'web_url',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->string('item_id')->nullable();
            $table->string('web_url')->nullable();
            $table->string('drive_id')->nullable();
        });
    }
};
