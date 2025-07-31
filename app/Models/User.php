<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $table = 'users';

    protected $fillable = [
        'username',
        'password', // Sẽ được hash trước khi lưu
        'avatar',
        'full_name',
        'email',
        'phone_number',
        'status',            // Trạng thái tài khoản (active, inactive, banned)
        'email_verified_at',
        'last_login',
        'remember_token',
        'deleted_at', // Thời điểm xóa mềm (nếu có)
    ];


    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BLOCKED = 'blocked';
    const ROLE_ADMIN = 'admin';
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Relationship với Order (đơn hàng được tạo bởi user)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relationship với OrderItemChangeLog (lịch sử thay đổi được thực hiện bởi user)
     */
    public function orderItemChangeLogs(): HasMany
    {
        return $this->hasMany(OrderItemChangeLog::class);
    }

    public function hasPermission($permissionName)
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('permission_name')
            ->contains($permissionName);
    }

    public function getAllPermissions(): \Illuminate\Support\Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('permission_name')
            ->unique();
    }
    public function hasRole(string $roleName): bool
    {
        return $this->roles->pluck('role_name')->contains($roleName);
    }

    public function getPrimaryRoleName(): ?string
    {
        return $this->roles()->first()?->role_name;
    }

}
