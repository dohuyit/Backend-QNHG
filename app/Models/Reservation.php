<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'reservations';

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'reservation_time',
        'reservation_date',    // Ví dụ: '2023-10-01'
        'number_of_guests',     // Ví dụ: 4
        'table_id',             // ID bàn được chỉ định (nếu có)
        'notes',                // Ví dụ: "Xin bàn gần cửa sổ, không hút thuốc."
        'status',               // 'pending', 'confirmed', 'cancelled', 'completed', 'no_show', 'seated'
        'user_id',              // ID nhân viên tạo/xác nhận đặt bàn
        'confirmed_at',
        'cancelled_at',
        'completed_at',
    ];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function tables()
    {
        return $this->belongsToMany(Table::class, 'reservation_tables', 'reservation_id', 'table_id');
    }
}
