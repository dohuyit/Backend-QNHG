<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $fillable = [
        'branch_id',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_time',     // Ví dụ: "2025-06-15 19:30:00"
        'number_of_guests',     // Ví dụ: 4
        'table_id',             // ID bàn được chỉ định (nếu có)
        'promotion_id',         // ID khuyến mãi áp dụng (nếu có)
        'notes',                // Ví dụ: "Xin bàn gần cửa sổ, không hút thuốc."
        'status',               // 'pending', 'confirmed', 'cancelled', 'completed', 'no_show', 'seated'
        'user_id',              // ID nhân viên tạo/xác nhận đặt bàn
        'confirmed_at',
        'cancelled_at',
        'completed_at',
    ];
}
