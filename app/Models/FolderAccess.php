<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FolderAccess extends Model
{
    protected $fillable = [
        'folder_id',
        'accessible_id',
        'accessible_type',
    ];

    public function folder()
    {
        try {
            return $this->belongsTo(Folder::class);
        } catch (\Exception $e) {
            Log::error('FolderAccess::folder failed: ' . $e->getMessage());
            return null;
        }
    }

    public function accessible()
    {
        try {
            return $this->morphTo();
        } catch (\Exception $e) {
            Log::error('FolderAccess::accessible failed: ' . $e->getMessage());
            return null;
        }
    }
}
