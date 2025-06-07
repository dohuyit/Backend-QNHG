<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'permissions';

    protected $fillable = [
        'permission_name',      // Ví dụ: "view-orders", "create-menu-item", "delete-user" (thường là slug-like)
        'permission_group_id',  // ID của nhóm quyền
        'description',          // Ví dụ: "Cho phép xem danh sách đơn hàng."
    ];
}
