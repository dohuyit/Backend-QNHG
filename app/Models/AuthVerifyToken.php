<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuthVerifyToken extends Model
{
    protected $table = 'auth_verify_tokens'; // Tên bảng

    protected $fillable = [
        'user_id',
        'token',
        'description',
        'status',
        'expired_at',
    ];
}
