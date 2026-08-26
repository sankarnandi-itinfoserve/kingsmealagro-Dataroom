<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Group extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'created_by',
    ];

    public function users()
    {
        try {
            return $this->belongsToMany(User::class, 'group_user')->withTimestamps();
        } catch (\Exception $e) {
            Log::error('Group::users failed: ' . $e->getMessage());
            return null;
        }
    }

    public function creator()
    {
        try {
            return $this->belongsTo(User::class, 'created_by');
        } catch (\Exception $e) {
            Log::error('Group::creator failed: ' . $e->getMessage());
            return null;
        }
    }

    public function folderAccesses()
    {
        try {
            return $this->morphMany(FolderAccess::class, 'accessible');
        } catch (\Exception $e) {
            Log::error('Group::folderAccesses failed: ' . $e->getMessage());
            return null;
        }
    }
}
