<?php

namespace App\Models;

use App\Helper\Helper;
use App\Notifications\ResetPasswordNotification;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Log;

// #[Fillable(['name', 'email', 'password'])]
// #[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;
    use HasRoles;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'emp_id',
        'fname',
        'lname',
        'username',
        'email',
        'avatar',
        'password',
        'temp_password',
        'initials',
        'replay_email',
        'office_phone',
        'mobile_no',
        'direct_phone',
        'division',
        'billing_dept',
        'street_address',
        'city',
        'state',
        'postal_code',
        'country',
        'role',
        'pr_acc',
        'all_comp_access',
        'ar_reg_access',
        'user_type',
        'job_title',
        'manager_id',
        'backup_manager_id',
        'timezone',
        'locale',
        'email_verified_at',
        'active',
        'displayName',
        'all_business_unit_access',
        'is_access_target_tracker'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): ?array
    {
        try {
            return [
                'email_verified_at' => 'datetime',
                // 'password' => 'hashed',
            ];
        } catch (\Exception $e) {
            Log::error('User::casts failed: ' . $e->getMessage());
            return null;
        }
    }
    public function getFullNameAttribute(): ?string
    {
        try {
            return trim(($this->fname ?? '') . ' ' . ($this->lname ?? '')) ?: ($this->displayName ?? 'User');
        } catch (\Exception $e) {
            Log::error('User::getFullNameAttribute failed: ' . $e->getMessage());
            return null;
        }
    }

    public function getAvatarUrlAttribute(): ?string
    {
        try {
            if ($this->avatar) {
                return asset('storage/' . $this->avatar);
            }
            $img = ($this->id % 70) ?: 70;
            return "https://i.pravatar.cc/200?img={$img}";
        } catch (\Exception $e) {
            Log::error('User::getAvatarUrlAttribute failed: ' . $e->getMessage());
            return null;
        }
    }

    public function sendPasswordResetNotification($token): void
    {
        try {
            $this->notify(new ResetPasswordNotification($token));
        } catch (\Exception $e) {
            Log::error('User::sendPasswordResetNotification failed: ' . $e->getMessage());
        }
    }

    public function groups()
    {
        try {
            return $this->belongsToMany(Group::class, 'group_user')->withTimestamps();
        } catch (\Exception $e) {
            Log::error('User::groups failed: ' . $e->getMessage());
            return null;
        }
    }

    public function folderAccesses()
    {
        try {
            return $this->morphMany(FolderAccess::class, 'accessible');
        } catch (\Exception $e) {
            Log::error('User::folderAccesses failed: ' . $e->getMessage());
            return null;
        }
    }

}
