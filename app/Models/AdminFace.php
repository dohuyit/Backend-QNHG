<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminFace extends Model
{
    use HasFactory;

    protected $table = 'admin_faces';

    protected $fillable = [
        'user_id',
        'email',
        'full_name',
        'role_name',
        'face_encoding',
        'is_trained'
    ];

    protected $casts = [
        'is_trained' => 'boolean',
        'face_encoding' => 'array'
    ];

    /**
     * Quan hệ với bảng users (nếu có)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope để lấy những user đã được training
     */
    public function scopeTrained($query)
    {
        return $query->where('is_trained', true);
    }

    /**
     * Scope để lấy theo role
     */
    public function scopeByRole($query, $role)
    {
        return $query->where('role_name', $role);
    }

    /**
     * Lấy danh sách roles có sẵn
     */
    public static function getRoles()
    {
        return ['Admin', 'Quản lý bếp', 'Nhân viên'];
    }

    /**
     * Kiểm tra xem user có quyền admin không
     */
    public function isAdmin()
    {
        return $this->role_name === 'Admin';
    }

    /**
     * Kiểm tra xem user có quyền bếp không
     */
    public function isKitchen()
    {
        return $this->role_name === 'Quản lý bếp';
    }

    /**
     * Kiểm tra xem user có quyền nhân viên không
     */
    public function isStaff()
    {
        return $this->role_name === 'Nhân viên';
    }
}
