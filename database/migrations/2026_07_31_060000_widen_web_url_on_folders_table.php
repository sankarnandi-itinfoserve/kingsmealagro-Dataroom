<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Was varchar(255) — long file/folder names (or SharePoint's URL
        // encoding of special characters) can push a real webUrl well past
        // that, causing inserts to fail outright during sync/import instead
        // of just truncating silently. download_url/response already use
        // text for the same reason. Raw SQL since doctrine/dbal (needed for
        // Schema::table()->change()) isn't installed in this app.
        DB::statement('ALTER TABLE folders MODIFY web_url TEXT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE folders MODIFY web_url VARCHAR(255) NULL');
    }
};
