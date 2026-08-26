<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('role_folder_accesses', function (Blueprint $table) {
            // JSON array of permission codes: R=Read Only, E=Edit, A=All Access, D=Delete
            // Example stored value: ["R","E"]
            $table->string('folder_permission', 500)->default('["R"]')->after('folder_id');
        });
    }

    public function down(): void
    {
        Schema::table('role_folder_accesses', function (Blueprint $table) {
            $table->dropColumn('folder_permission');
        });
    }
};
