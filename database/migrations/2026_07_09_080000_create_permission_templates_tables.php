<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('permission_templates')) {
            Schema::create('permission_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('permission_template_items')) {
            Schema::create('permission_template_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('permission_template_id');
                $table->string('folder_name');
                $table->string('permission', 1); // R, M, A
                $table->timestamps();

                $table->foreign('permission_template_id')
                    ->references('id')->on('permission_templates')
                    ->onDelete('cascade');
                    // Unique index added in a later migration — MySQL's default
                    // auto-generated name for this exceeds the 64-char identifier limit.
            });
        }

        if (!Schema::hasTable('permission_template_role')) {
            Schema::create('permission_template_role', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('permission_template_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();

                $table->foreign('permission_template_id')
                    ->references('id')->on('permission_templates')
                    ->onDelete('cascade');
                $table->unique(['permission_template_id', 'role_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_template_role');
        Schema::dropIfExists('permission_template_items');
        Schema::dropIfExists('permission_templates');
    }
};
