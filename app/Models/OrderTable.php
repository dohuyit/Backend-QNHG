<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTable extends Model
{
    protected $table = 'order_tables';

    protected $fillable = [
        'order_id',
        'table_id',
        'notes',
    ];

    /**
     * Relationship với Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship với Table
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }
}
