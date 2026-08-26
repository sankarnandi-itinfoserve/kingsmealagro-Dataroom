<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class File extends Model
{
    protected $fillable = [
        'folder_id',
        'name',
        'file_path',
        'file_type',
        'size',
        'uploaded_by'
    ];

    public function folder()
    {
        try {
            return $this->belongsTo(Folder::class);
        } catch (\Exception $e) {
            Log::error('File::folder failed: ' . $e->getMessage());
            return null;
        }
    }

    public function uploader()
    {
        try {
            return $this->belongsTo(User::class, 'uploaded_by');
        } catch (\Exception $e) {
            Log::error('File::uploader failed: ' . $e->getMessage());
            return null;
        }
    }
}
