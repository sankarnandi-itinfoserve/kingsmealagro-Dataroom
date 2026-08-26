<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drops the whole role-based folder access system, plus the Permission
     * Templates feature that only existed to feed it. Any authenticated user
     * now has full access to every project/folder/file — there is deliberately
     * no replacement access-control layer.
     *
     * Child tables (both carry FKs onto permission_templates) drop first, or
     * MySQL rejects the parent drop with a constraint error.
     */
    public function up(): void
    {
        Schema::dropIfExists('role_folder_accesses');

        Schema::dropIfExists('permission_template_items');
        Schema::dropIfExists('permission_template_role');
        Schema::dropIfExists('permission_templates');

        if (Schema::hasColumn('roles', 'default_folder_access')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropColumn('default_folder_access');
            });
        }
    }

    /**
     * Structural reversal only — recreates the tables/column as they stood at
     * the moment they were dropped (all later ALTERs folded in). The rows
     * themselves are gone for good, and no application code reads any of this
     * any more, so a restored schema stays empty and unused.
     */
    public function down(): void
    {
        if (!Schema::hasTable('role_folder_accesses')) {
            Schema::create('role_folder_accesses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('folder_id');
                // JSON array of permission codes, e.g. ["R","D"]
                $table->string('folder_permission', 500)->default('["R"]');
                $table->timestamps();

                $table->unique(['role_id', 'folder_id']);
            });
        }

        if (!Schema::hasTable('permission_templates')) {
            Schema::create('permission_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('permission_template_items')) {
            Schema::create('permission_template_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('permission_template_id');
                $table->foreignId('folder_id')->nullable()->constrained('folders')->nullOnDelete();
                $table->string('folder_name');
                $table->string('permission', 1); // R, M, A
                $table->timestamps();

                $table->foreign('permission_template_id')
                    ->references('id')->on('permission_templates')
                    ->onDelete('cascade');
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

        if (!Schema::hasColumn('roles', 'default_folder_access')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->boolean('default_folder_access')->default(true);
            });
        }
    }
};
