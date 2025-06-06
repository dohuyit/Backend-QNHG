<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table = 'user_roles';

    protected $fillable = [
        'user_id',
        'role_id',
        'branch_id',        // ID chi nhánh mà vai trò này được áp dụng cho người dùng
    ];
}
