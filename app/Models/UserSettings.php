<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSettings extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'home_division', 'admin_private_bid'];
}
