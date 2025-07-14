<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
      use HasFactory;

    protected $fillable = [
        'cart_id',      // Mã giỏ hàng
        'dish_id',      // Mã món ăn
        'combo_id',     // Mã combo
        'quantity',     // Số lượng
        'price',        // Giá tại thời điểm thêm
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class);
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class);
    }
}
