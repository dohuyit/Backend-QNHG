<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'role_name',        // Ví dụ: "Admin", "Quản lý chi nhánh", "Thu ngân", "Phục vụ", "Bếp"
        'slug',             // Ví dụ: "admin", "branch-manager"
        'description',      // Ví dụ: "Có toàn quyền quản trị hệ thống."
    ];
}
