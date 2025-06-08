<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $table = 'promotions';

    protected $fillable = [
        'name',                     // Ví dụ: "Giảm giá 20% Thứ Tư Vui Vẻ"
        'slug',                     // Ví dụ: "giam-gia-20-thu-tu-vui-ve"
        'description',              // Ví dụ: "Áp dụng cho tất cả hóa đơn vào Thứ Tư hàng tuần."
        'type',                     // 'percentage', 'fixed'
        'discount_value',           // Ví dụ: 20 (cho type percentage) hoặc 50000 (cho type fixed)
        'min_order_amount',         // Ví dụ: 300000.00 (đơn hàng tối thiểu 300k)
        'coupon_code',              // Ví dụ: "THUTUVUIVE" (nếu có)
        'start_date',               // Ví dụ: "2025-06-01 00:00:00"
        'end_date',                 // Ví dụ: "2025-06-30 23:59:59"
        'tags',                     // Ví dụ (JSON): ["ngày trong tuần", "toàn bộ menu", "khách hàng mới"]
        'usage_limit',              // Ví dụ: 1000 (giới hạn 1000 lượt sử dụng)
        'usage_limit_per_customer', // Ví dụ: 1 (mỗi khách dùng 1 lần)
        'current_usage_count',      // Tự động tăng, không nên fillable
        'is_active',                // Ví dụ: true
        'user_id',                  // ID nhân viên tạo khuyến mãi
    ];
}
