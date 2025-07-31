<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;
    protected $table = "discount_codes";
    protected $fillable = [
        'code', 'type', 'value',
        'start_date', 'end_date',
        'usage_limit', 'used', 'is_active'
    ];
}
