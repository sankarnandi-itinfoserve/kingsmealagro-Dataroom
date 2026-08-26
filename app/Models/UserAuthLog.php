<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class UserAuthLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'logged_in',
        'logon_type',
        'logged_out',
        'device_info',
        'device_token',
        'remember_expires_at',
    ];

    public function user()
    {
        try {
            return $this->belongsTo(User::class);
        } catch (\Exception $e) {
            Log::error('UserAuthLog::user failed: ' . $e->getMessage());
            return null;
        }
    }
}
