<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('folder_subscriptions', 'excluded')) {
            Schema::table('folder_subscriptions', function (Blueprint $table) {
                // A row now records the user's explicit choice at that exact
                // folder — excluded=false means "notify me here", excluded=true
                // means "don't notify me here even though a parent folder is
                // subscribed". The closest explicit row (this folder, else its
                // nearest ancestor) wins when resolving who to notify.
                $table->boolean('excluded')->default(false)->after('folder_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('folder_subscriptions', 'excluded')) {
            Schema::table('folder_subscriptions', function (Blueprint $table) {
                $table->dropColumn('excluded');
            });
        }
    }
};
