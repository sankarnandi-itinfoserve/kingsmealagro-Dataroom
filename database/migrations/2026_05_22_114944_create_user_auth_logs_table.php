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
        Schema::create('user_auth_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users','id');
            $table->timestamp('logged_in');
            $table->string('logon_type');
            $table->timestamp('logged_out')->nullable();
            $table->text('device_info')->nullable();
            $table->text('logs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('user_auth_logs');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
