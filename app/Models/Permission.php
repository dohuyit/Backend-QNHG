<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use SoftDeletes;
    protected $table = 'permissions';

    protected $fillable = [
        'permission_name',      // Ví dụ: "view-orders", "create-menu-item", "delete-user" (thường là slug-like)
        'permission_group_id',  // ID của nhóm quyền
        'description',          // Ví dụ: "Cho phép xem danh sách đơn hàng."
    ];
    public function permissionGroup()
    {
        return $this->belongsTo(PermissionGroup::class);
    }
}
