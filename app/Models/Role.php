<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use SoftDeletes;
    protected $table = 'roles';

    protected $fillable = [
        'role_name',        // Ví dụ: "Admin", "Quản lý chi nhánh", "Thu ngân", "Phục vụ", "Bếp"
        'description',      // Ví dụ: "Có toàn quyền quản trị hệ thống."
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions->pluck('permission_name')->contains($permissionName);
    }

}