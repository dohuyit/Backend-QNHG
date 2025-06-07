<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
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
}
