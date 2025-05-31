<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCombo extends Model
{
    protected $table = 'user_combos';
    protected $fillable = [
        'user_id',          // ID của người dùng (nhân viên) tạo combo này
        'combo_name',       // Ví dụ: "Combo trưa yêu thích của Sếp"
        'slug',             // Ví dụ: "combo-trua-yeu-thich-sep"
        'description',      // Ví dụ: "Gồm các món sếp hay ăn trưa."
        'price',            // Ví dụ: 150000.00 (nếu có giá cố định)
        'is_active',        // Ví dụ: true
    ];
}
