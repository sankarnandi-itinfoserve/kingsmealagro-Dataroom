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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('fname')->nullable(); 
            $table->string('lname')->nullable(); 
            $table->string('username'); 
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('emp_id');                    
            $table->string('avatar')->nullable();            
            $table->string('temp_password')->nullable();
            $table->string('initials')->nullable();
            $table->string('replay_email')->unique();
            $table->string('office_phone')->nullable();
            $table->string('mobile_no')->nullable();
            $table->string('direct_phone')->nullable();
            $table->string('division')->nullable();
            $table->string('billing_dept')->nullable();
            $table->string('role');
            $table->tinyInteger('all_comp_access')->default(0);
            $table->tinyInteger('pr_acc')->default(0);  
            $table->string('user_type')->nullable();
            $table->string('job_title')->nullable();
            $table->bigInteger('manager_id')->nullable();            
            $table->bigInteger('backup_manager_id')->nullable();
            $table->boolean('is_access_target_tracker')->default(false);
            $table->string('timezone')->nullable();
            $table->string('locale')->default('EN');
            $table->softDeletesTz();
            $table->boolean('active')->default(false);
            $table->string('azure_id');
            $table->string('displayName');
            $table->tinyInteger('ar_reg_access')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
