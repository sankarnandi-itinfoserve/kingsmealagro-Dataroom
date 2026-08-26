<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // The composite (user_id, device_token) index is the only thing
        // currently backing the user_id foreign key, so it has to be dropped
        // and re-added around removing that index.
        Schema::table('user_auth_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('user_auth_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'device_token']);
            $table->dropColumn([
                'device_token',
                'remember_expires_at',
            ]);
        });

        Schema::table('user_auth_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'azure_id',
                'mfa_code',
                'mfa_expires_at',
                'mfa_enabled',
            ]);
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('user_social_creds');
        Schema::dropIfExists('allowed_microsoft_groups');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('allowed_microsoft_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_id');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('user_social_creds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'id');
            $table->string('social_id')->nullable();
            $table->string('social_type')->nullable();
            $table->text('social_access_token')->nullable();
            $table->text('social_refresh_token')->nullable();
            $table->text('auth_res')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('azure_id')->default('');
            $table->string('mfa_code')->nullable();
            $table->timestamp('mfa_expires_at')->nullable();
            $table->boolean('mfa_enabled')->default(true);
        });

        Schema::table('user_auth_logs', function (Blueprint $table) {
            $table->string('device_token')->nullable();
            $table->timestamp('remember_expires_at')->nullable();
            $table->index(['user_id', 'device_token']);
        });
    }
};
