<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationChangeLog extends Model
{
    protected $table = 'reservation_change_logs';

    protected $fillable = [
        'reservation_id',
        'user_id',              // ID nhân viên thực hiện thay đổi
        'change_timestamp',     // Thường tự động set
        'change_type',          // Ví dụ: "STATUS_UPDATE", "GUESTS_ADJUSTED"
        'field_changed',        // Ví dụ: "status", "number_of_guests"
        'old_value',            // Ví dụ: "pending"
        'new_value',            // Ví dụ: "confirmed"
        'description',          // Ví dụ: "Khách hàng gọi điện xác nhận đặt bàn."
    ];

    public $timestamps = false;
}
