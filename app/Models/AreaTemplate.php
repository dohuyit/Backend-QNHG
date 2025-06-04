<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaTemplate extends Model
{
    protected $table = 'area_templates'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'name',
        'description',
    ];
}
