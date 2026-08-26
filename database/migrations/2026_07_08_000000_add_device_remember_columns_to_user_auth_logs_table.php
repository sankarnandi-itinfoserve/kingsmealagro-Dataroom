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
        Schema::table('user_auth_logs', function (Blueprint $table) {
            $table->string('device_token')->nullable();
            $table->timestamp('remember_expires_at')->nullable();
            $table->index(['user_id', 'device_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_auth_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'device_token']);
            $table->dropColumn([
                'device_token',
                'remember_expires_at',
            ]);
        });
    }
};
