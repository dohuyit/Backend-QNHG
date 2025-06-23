<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'orders'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'order_code',           // Tự sinh hoặc theo quy tắc
        'order_type',           // 'dine-in', 'takeaway', 'delivery'
        'table_id',             // Cho 'dine-in'
        'reservation_id',       // Nếu đơn hàng từ đặt bàn
        'user_id',              // Nhân viên tạo đơn
        'customer_id',
        'order_time',           // Thường tự động set
        'status',               // 'pending_confirmation', 'confirmed', 'preparing', ...
        'payment_status',       // 'unpaid', 'partially_paid', 'paid', 'refunded'
        'notes',                // Ghi chú chung cho đơn hàng
        'delivery_address',
        'delivery_contact_name',
        'delivery_contact_phone',
        'total_amount',         // Tổng tiền các món (trước giảm giá, phí)
        'final_amount',         // Tổng tiền cuối cùng (tham chiếu từ Bill)
        'preparation_time_estimated_minutes',
        'preparation_time_actual_minutes',
        'pickup_time',
        'delivery_dispatched_at',
        'delivered_at',
    ];

    /**
     * Relationship với OrderItem
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship với OrderTable
     */
    public function tables(): HasMany
    {
        return $this->hasMany(OrderTable::class);
    }

    /**
     * Relationship với Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship với User (nhân viên tạo đơn)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
