<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TableArea extends Model
{
    use HasFactory;
    protected $table = 'table_areas';

    protected $fillable = [
        'name',              // Tên khu vực bàn
        'description',       // Mô tả khu vực bàn
        'capacity',          // Sức chứa tối đa của khu vực bàn
        'status',            // Trạng thái khu vực bàn (active, inactive)
    ];
    public $timestamps = true;
}
