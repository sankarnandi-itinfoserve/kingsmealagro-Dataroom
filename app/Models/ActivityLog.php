<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'description',
        'properties',
        'ip_address',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user()
    {
        try {
            return $this->belongsTo(User::class);
        } catch (\Exception $e) {
            Log::error('ActivityLog::user failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Short, human-friendly name for the model_type column
     * (e.g. "App\Models\Folder" => "Folder").
     */
    public function getModelLabelAttribute(): ?string
    {
        try {
            if ($this->model_type === \App\Models\UserAuthLog::class) {
                return $this->action === 'logout' ? 'Logout' : 'Login';
            }

            $parts = explode('\\', $this->model_type ?? '');
            return end($parts) ?: 'Item';
        } catch (\Exception $e) {
            Log::error('ActivityLog::getModelLabelAttribute failed: ' . $e->getMessage());
            return null;
        }
    }
}
