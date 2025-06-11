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
    protected $dates = ['expired_at', 'deleted_at'];
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
