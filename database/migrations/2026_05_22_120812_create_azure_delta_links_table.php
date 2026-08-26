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
        Schema::create('azure_delta_links', function (Blueprint $table) {
            $table->id();
            $table->longText('delta_url')->nullable();
            $table->longText('delta_url_update')->nullable();
            $table->string('group_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('azure_delta_links');
    }
};
