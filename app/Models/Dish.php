<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dish extends Model
{
    use SoftDeletes;
    protected $table = 'dishes';

    protected $fillable = [
        'category_id',      // ID của danh mục món ăn
        'name',             // Ví dụ: "Phở Bò Tái Chín", "Cơm Sườn Nướng Mật Ong"
        'slug',             // Ví dụ: "pho-bo-tai-chin", "com-suon-nuong-mat-ong"
        'description',      // Ví dụ: "Nước dùng đậm đà, thịt bò tươi ngon, bánh phở mềm mại."
        'original_price',   // Ví dụ: 60000.00
        'selling_price',    // Ví dụ: 55000.00 (nếu có khuyến mãi)
        'unit',             // Ví dụ: "bát", "đĩa", "suất", "ly"
        'image_url',        // Ví dụ: "/images/menu_items/pho-bo.jpg"
        'tags',             // Ví dụ (JSON): ["best-seller", "món truyền thống", "cay nồng"]
        'is_featured',      // Ví dụ: true (món nổi bật)
        'is_active',        // Ví dụ: true (đang bán)
    ];
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

}
