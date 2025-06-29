<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'dish_id',         // Nếu là món lẻ
        'combo_id',             // Nếu là combo
        'quantity',
        'unit_price',           // Giá tại thời điểm đặt
        'notes',                // Ghi chú cho món này
        'kitchen_status',       // 'pending', 'preparing', 'ready', 'served', 'cancelled'
        'is_priority',
    ];

    /**
     * Relationship với Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship với Dish (MenuItem)
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'dish_id');
    }

    /**
     * Relationship với Combo (nếu có)
     */
    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderItemChangeLog::class);
    }
    public function kitchenOrder()
    {
        return $this->hasOne(KitchenOrder::class);
    }
}
