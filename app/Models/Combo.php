<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    protected $table = 'combos';
    protected $fillable = [
        'name',                 // Ví dụ: "Combo Gia Đình Vui Vẻ", "Set Lẩu Thái Đặc Biệt"
        'slug',                 // Ví dụ: "combo-gia-dinh-vui-ve"
        'description',          // Ví dụ: "Bao gồm 2 món chính, 1 món khai vị và 4 đồ uống."
        'original_total_price', // Ví dụ: 550000.00 (tổng giá gốc các món lẻ)
        'selling_price',        // Ví dụ: 499000.00 (giá bán combo)
        'image_url',            // Ví dụ: "/images/combos/gia-dinh.jpg"
        'tags',                 // Ví dụ (JSON): ["cho 4 người", "tiết kiệm", "cuối tuần"]
        'is_active',            // Ví dụ: true (đang áp dụng)
    ];
}
