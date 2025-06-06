<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenOrder extends Model
{
    protected $table = 'kitchen_orders'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'order_item_id',        // UNIQUE
        'order_id',
        'table_number',
        'item_name',
        'quantity',
        'notes',
        'status',               // 'pending', 'preparing', 'ready', 'cancelled'
        'is_priority',
        'received_at',          // Thường tự động set
        'completed_at',
    ];
}
