<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inbox_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('folder_id')->nullable();
            $table->string('item_name');
            $table->string('actor_name')->nullable();
            $table->string('folder_name')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('inbox_notifications'); }
};
