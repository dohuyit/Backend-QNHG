<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTable extends Model
{
    protected $table = 'order_tables';
    protected $fillable = [
        'order_id',
        'table_id',
        'notes',
    ];
}
