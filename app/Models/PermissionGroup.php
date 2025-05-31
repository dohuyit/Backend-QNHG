<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    protected $table = 'permission_groups';
    protected $fillable = [
        'group_name',       // Ví dụ: "Quản lý Thực đơn", "Quản lý Đơn hàng", "Quản lý Người dùng"
        'slug',             // Ví dụ: "menu-management", "order-management"
        'description',      // Ví dụ: "Nhóm các quyền liên quan đến quản lý thực đơn món ăn."
    ];
}
