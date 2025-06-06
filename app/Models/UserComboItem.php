<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserComboItem extends Model
{
    protected $table = 'user_combo_items';

    protected $fillable = [
        'user_combo_id',    // ID của UserCombo
        'dish_id',     // ID của món ăn
        'quantity',         // Ví dụ: 1
    ];
}
