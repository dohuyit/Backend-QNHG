<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    protected $table = 'tables';

    protected $fillable = [
        'table_area_id',    // ID của khu vực bàn
        'table_number',     // Ví dụ: "A10", "VIP02", "Bar-05"
        'capacity',         // Ví dụ: 4 (bàn 4 người)
        'min_guests',       // Ví dụ: 2
        'max_guests',       // Ví dụ: 6
        'description',      // Ví dụ: "Bàn gần cửa sổ, view đẹp", "Bàn tròn lớn"
        'tags',             // Ví dụ (JSON): ["yên tĩnh", "view đẹp", "ghế sofa"]
        'status',           // Ví dụ: 'available', 'occupied', 'reserved', 'cleaning', 'out_of_service'
        'is_active',        // Ví dụ: true, false
    ];
}
