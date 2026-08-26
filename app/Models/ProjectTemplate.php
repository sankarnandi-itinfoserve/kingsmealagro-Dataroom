<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ProjectTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'folder_structure',
        'source_folder_id',
        'created_by',
    ];

    protected $casts = [
        'folder_structure' => 'array',
    ];

    public function creator()
    {
        try {
            return $this->belongsTo(User::class, 'created_by');
        } catch (\Exception $e) {
            Log::error('ProjectTemplate::creator failed: ' . $e->getMessage());
            return null;
        }
    }
}
