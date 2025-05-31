<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboItem extends Model
{
    protected $table = 'combo_items';
    protected $fillable = [
        'combo_id',         // ID của combo
        'menu_item_id',     // ID của món ăn trong combo
        'quantity',         // Ví dụ: 1, 2
    ];
}
