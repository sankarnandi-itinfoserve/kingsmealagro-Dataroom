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
        Schema::create('folder_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained()->cascadeOnDelete();
            $table->morphs('accessible');
            $table->timestamps();

            $table->unique(['folder_id', 'accessible_id', 'accessible_type'], 'folder_access_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folder_accesses');
    }
};
