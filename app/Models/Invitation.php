<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class Invitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'expires_at',
        'accepted',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    // Check if invitation is expired
    public function isExpired()
    {
        try {
            return $this->expires_at && now()->gt($this->expires_at);
        } catch (\Exception $e) {
            Log::error('Invitation::isExpired failed: ' . $e->getMessage());
            return null;
        }
    }

    // Check if invitation is already accepted
    public function isAccepted()
    {
        try {
            return $this->accepted;
        } catch (\Exception $e) {
            Log::error('Invitation::isAccepted failed: ' . $e->getMessage());
            return null;
        }
    }

    // Mark as accepted
    public function markAsAccepted()
    {
        try {
            $this->update([
                'accepted' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Invitation::markAsAccepted failed: ' . $e->getMessage());
        }
    }
}
