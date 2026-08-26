<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            // Points at the real master-template folder (under an
            // is_template=1 scaffold) this folder was cloned from when its
            // project's template got assigned — non-null marks "this folder
            // was created from a template" and identifies exactly which
            // source folder, at any depth (not just the top-level item).
            $table->foreignId('template_source_folder_id')->nullable()->after('permission_template_id')
                ->constrained('folders', 'id', 'folders_template_source_folder_id_foreign')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_source_folder_id');
        });
    }
};
