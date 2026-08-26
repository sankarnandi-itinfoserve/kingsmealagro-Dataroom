<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The SharePoint/Microsoft Graph integration was removed entirely — project
 * files now live in local app storage (storage/app/public/project_folders,
 * keyed by folders.id — see Folder::localDiskPath()). Nothing reads or writes
 * these tables any more, and no other table has a foreign key into them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('azure_subscriptions');
        Schema::dropIfExists('azure_delta_links');
        Schema::dropIfExists('azure_access_tokens');
    }

    /**
     * Best-effort recreation of the empty table shells only. The integration
     * itself is gone for good — rolling this back restores the structures,
     * not the feature.
     */
    public function down(): void
    {
        Schema::create('azure_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->longText('token')->nullable();
            $table->timestamps();
        });

        Schema::create('azure_delta_links', function (Blueprint $table) {
            $table->id();
            $table->longText('delta_url')->nullable();
            $table->longText('delta_url_update')->nullable();
            $table->string('group_id');
            $table->timestamps();
        });

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
};
