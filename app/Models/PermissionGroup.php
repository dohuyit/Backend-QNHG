<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermissionGroup extends Model
{
    use SoftDeletes;
    protected $table = 'permission_groups';

    protected $fillable = [
        'group_name',       // Ví dụ: "Quản lý Thực đơn", "Quản lý Đơn hàng", "Quản lý Người dùng"
        'description',      // Ví dụ: "Nhóm các quyền liên quan đến quản lý thực đơn món ăn."
    ];

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
