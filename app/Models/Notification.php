<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'message',
        'type',
        'reservation_id',
        'kitchen_order_id',
        'order_id',
        'bill_id',
        'receiver_id',
        'receiver_role',
        'read_at',
    ];

    /**
     * Người nhận thông báo.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Đặt bàn liên quan (nếu có).
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
    }

    /**
     * Đơn món bếp liên quan (nếu có).
     */
    public function kitchenOrder()
    {
        return $this->belongsTo(KitchenOrder::class, 'kitchen_order_id');
    }

    /**
     * Đơn hàng liên quan (nếu có).
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Hoá đơn liên quan (nếu có).
     */
    public function bill()
    {
        return $this->belongsTo(Bill::class, 'bill_id');
    }
}
