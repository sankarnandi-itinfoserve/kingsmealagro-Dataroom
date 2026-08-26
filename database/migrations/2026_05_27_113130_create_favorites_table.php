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
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();


           
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['folder_id']);
        });
        Schema::dropIfExists('favorites');
    }
};
