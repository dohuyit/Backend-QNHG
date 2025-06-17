<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;
    protected $table = 'roles';

    protected $fillable = [
        'role_name',        // Ví dụ: "Admin", "Quản lý chi nhánh", "Thu ngân", "Phục vụ", "Bếp"
        'description',      // Ví dụ: "Có toàn quyền quản trị hệ thống."
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

}
