<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComboItem extends Model
{
    protected $table = 'combo_items';

    protected $fillable = [
        'combo_id',         // ID của combo
        'dish_id',     // ID của món ăn trong combo
        'quantity',         // Ví dụ: 1, 2
    ];

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class, 'dish_id');
    }
}
