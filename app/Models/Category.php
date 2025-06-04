<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $fillable = [
        'name',             // Ví dụ: "Món Khai Vị", "Món Chính", "Tráng Miệng", "Đồ Uống"
        'slug',             // Ví dụ: "mon-khai-vi", "mon-chinh"
        'description',      // Ví dụ: "Các món ăn nhẹ nhàng để bắt đầu bữa tiệc."
        'image_url',        // Ví dụ: "/images/categories/khai-vi.jpg"
        'is_active',        // Ví dụ: true, false
    ];
}
