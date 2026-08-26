<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_social_creds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users','id');
            $table->string('social_id')->nullable();
            $table->string('social_type')->nullable();
            $table->text('social_access_token')->nullable();
            $table->text('social_refresh_token')->nullable();
            $table->text('auth_res')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('user_social_creds');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
