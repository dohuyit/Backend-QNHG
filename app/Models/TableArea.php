<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableArea extends Model
{
    protected $table = 'table_areas';

    protected $fillable = [
        'branch_id',         // ID chi nhánh
        'name',              // Tên khu vực bàn
        'slug',              // Slug cho khu vực bàn
        'description',       // Mô tả khu vực bàn
        'capacity',          // Sức chứa tối đa của khu vực bàn
        'status',            // Trạng thái khu vực bàn (active, inactive)
        'created_by',        // Người tạo khu vực bàn
        'updated_by',        // Người cập nhật khu vực bàn
    ];
}
