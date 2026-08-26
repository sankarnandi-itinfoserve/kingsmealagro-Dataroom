<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class RecentFile extends Model
{
    protected $fillable = [
        'user_id',
        'file_id',
        'view_count',
    ];

    public function user()
    {
        try {
            return $this->belongsTo(User::class);
        } catch (\Exception $e) {
            Log::error('RecentFile::user failed: ' . $e->getMessage());
            return null;
        }
    }

    public function folder()
    {
        try {
            return $this->belongsTo(Folder::class, 'file_id');
        } catch (\Exception $e) {
            Log::error('RecentFile::folder failed: ' . $e->getMessage());
            return null;
        }
    }
}
