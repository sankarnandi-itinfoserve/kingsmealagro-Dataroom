<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('folder_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('folder_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'folder_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('folder_subscriptions'); }
};
