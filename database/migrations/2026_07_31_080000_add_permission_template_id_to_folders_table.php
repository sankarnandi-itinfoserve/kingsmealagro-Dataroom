<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->foreignId('permission_template_id')->nullable()->after('is_template')
                ->constrained('permission_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('folders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('permission_template_id');
        });
    }
};
