<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class FolderShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'folder_id',
        'email',
        'permission'
    ];

    /* =========================
       RELATIONSHIPS
    ========================== */

    // Folder relation
    public function folder()
    {
        try {
            return $this->belongsTo(Folder::class);
        } catch (\Exception $e) {
            Log::error('FolderShare::folder failed: ' . $e->getMessage());
            return null;
        }
    }
}