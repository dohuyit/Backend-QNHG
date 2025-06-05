<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaTemplate extends Model
{
    use SoftDeletes;

    protected $table = 'area_templates'; // Tên bảng trong cơ sở dữ liệu

    protected $fillable = [
        'name',
        'description',
        'slug',
    ];
}
