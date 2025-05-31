<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    protected $table = 'bills'; // Tên bảng trong cơ sở dữ liệu
    protected $fillable = [
        'order_id',
        'customer_id',
        'bill_code',            // Tự sinh hoặc theo quy tắc
        'sub_total',
        'discount_amount',
        'vat_percentage',
        'vat_amount',
        'service_charge_percentage',
        'service_charge_amount',
        'delivery_fee',
        'final_amount',
        'status',               // 'unpaid', 'partially_paid', 'paid', 'cancelled', 'refunded'
        'issued_at',            // Thường tự động set
        'user_id',              // Nhân viên xuất hóa đơn
    ];
}
