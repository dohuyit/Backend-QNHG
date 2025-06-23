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
        'menu_item_id',         // Nếu là món lẻ
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
        return $this->belongsTo(Dish::class, 'menu_item_id');
    }

    /**
     * Relationship với Combo (nếu có)
     */
    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    /**
     * Relationship với OrderItemChangeLog (lịch sử thay đổi)
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderItemChangeLog::class);
    }
}
