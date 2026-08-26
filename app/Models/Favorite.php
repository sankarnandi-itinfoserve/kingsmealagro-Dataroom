<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Favorite extends Model
{
    protected $fillable = [
        'user_id',
        'folder_id',
        'created_by',
    ];

    /**
     * User who owns the favorite
     */
    public function user()
    {
        try {
            return $this->belongsTo(User::class);
        } catch (\Exception $e) {
            Log::error('Favorite::user failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Folder that is favorited
     */
    public function folder()
    {
        try {
            return $this->belongsTo(Folder::class);
        } catch (\Exception $e) {
            Log::error('Favorite::folder failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * User who created the favorite (optional)
     */
    public function creator()
    {
        try {
            return $this->belongsTo(User::class, 'created_by');
        } catch (\Exception $e) {
            Log::error('Favorite::creator failed: ' . $e->getMessage());
            return null;
        }
    }
}