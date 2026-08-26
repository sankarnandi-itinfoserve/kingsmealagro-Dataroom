<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_folder_accesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('folder_id');
            $table->timestamps();

            $table->unique(['role_id', 'folder_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_folder_accesses');
    }
};
