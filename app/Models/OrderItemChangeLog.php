<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemChangeLog extends Model
{
    protected $table = 'order_item_change_logs';

    protected $fillable = [
        'order_item_id',
        'order_id',
        'user_id',              // Nhân viên thực hiện
        'change_timestamp',     // Thường tự động set
        'change_type',          // Ví dụ: "QUANTITY_UPDATE", "ITEM_CANCELLED"
        'field_changed',        // Ví dụ: "quantity"
        'old_value',
        'new_value',
        'reason',
    ];

    /**
     * Relationship với OrderItem
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Relationship với Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship với User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
