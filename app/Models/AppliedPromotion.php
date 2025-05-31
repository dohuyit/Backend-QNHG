<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppliedPromotion extends Model
{
    protected $table = 'applied_promotions'; // Tên bảng trong cơ sở dữ liệu
    protected $fillable = [
        'bill_id',
        'promotion_id',
        'discount_applied',
        'coupon_code_used',
        'applied_at',           // Thường tự động set
        'customer_id',
    ];
}
