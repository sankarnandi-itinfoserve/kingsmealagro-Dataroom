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
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->bigInteger('size')->default(0);
            $table->timestamps();
    
            $table->foreign('parent_id')->references('id')->on('folders')->nullOnDelete(); 
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['created_by']);
        });
        Schema::dropIfExists('folders');
    }
};
