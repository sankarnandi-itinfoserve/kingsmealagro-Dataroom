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
        Schema::dropIfExists('folder_subscriptions');
        Schema::dropIfExists('inbox_notifications');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('folder_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->constrained()->cascadeOnDelete();
            $table->boolean('excluded')->default(false);
            $table->timestamps();
        });

        Schema::create('inbox_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->unsignedBigInteger('item_folder_id')->nullable();
            $table->string('item_name');
            $table->string('item_type', 20);
            $table->string('actor_name')->nullable();
            $table->string('folder_name')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
};
