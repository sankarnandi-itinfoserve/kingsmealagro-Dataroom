<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('azure_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('subscription_id')->unique();
            $table->string('resource');
            $table->string('drive_id');
            $table->string('client_state', 64);
            $table->dateTime('expiration_datetime');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('azure_subscriptions');
    }
};
