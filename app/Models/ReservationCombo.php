<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationCombo extends Model
{
    protected $table = 'reservation_combos';
    protected $fillable = [
        'reservation_id',
        'combo_id',
        'quantity',
        'note',                 // Ghi chú cho combo cụ thể trong đặt bàn
    ];
}
