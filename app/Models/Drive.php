<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Drive extends Model
{
    protected $fillable = ['name', 'drive_id', 'web_url', 'drive_type', 'description', 'response'];

    /**
     * Root-level folders shown under this drive. folders.parent_item_id is a
     * self-referential FK to folders.id now, so "root" simply means it has no
     * parent row — there is no drive-level linking column any more, hence a
     * plain query rather than an Eloquent relation.
     */
    public function rootFolders()
    {
        try {
            return Folder::whereNull('parent_item_id')->get();
        } catch (\Exception $e) {
            Log::error('Drive::rootFolders failed: ' . $e->getMessage());
            return collect();
        }
    }
}
