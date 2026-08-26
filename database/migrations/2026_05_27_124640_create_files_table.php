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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('name');
            $table->string('file_path');
            $table->string('file_type');
            $table->bigInteger('size')->default(0);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->foreign('folder_id')->references('id')->on('folders')->nullOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
