<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
