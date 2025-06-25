<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $table = 'bills'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'order_id',
        'bill_code',            // Mã hóa đơn, tự sinh hoặc theo quy tắc
        'sub_total',           // Tổng tiền hàng
        'discount_amount',     // Tiền giảm giá
        'delivery_fee',        // Phí giao hàng
        'final_amount',        // Tổng tiền cuối cùng
        'status',              // 'unpaid', 'paid', 'cancelled'
        'issued_at',           // Thời gian xuất hóa đơn, thường tự động set
        'user_id',             // Nhân viên xuất hóa đơn
    ];
}
