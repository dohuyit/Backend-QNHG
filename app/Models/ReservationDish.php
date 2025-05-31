<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationDish extends Model
{
    protected $table = 'reservation_dishes';
    protected $fillable = [
        'reservation_id',
        'menu_item_id',
        'quantity',
        'note',                 // Ghi chú cho món ăn cụ thể trong đặt bàn
    ];
}
