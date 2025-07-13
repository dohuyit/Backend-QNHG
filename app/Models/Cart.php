<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
      use HasFactory;

    protected $fillable = [
        'customer_id',      // Mã người dùng (nếu có)
        'total_amount',     // Tổng tiền trong giỏ
    ];

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
