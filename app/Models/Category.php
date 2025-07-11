<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use SoftDeletes;
    protected $table = 'categories';

    protected $fillable = [
        'name',             // Ví dụ: "Món Khai Vị", "Món Chính", "Tráng Miệng", "Đồ Uống"
        'description',      // Ví dụ: "Các món ăn nhẹ nhàng để bắt đầu bữa tiệc."
        'image_url',        // Ví dụ: "/images/categories/khai-vi.jpg"
        'is_active',        // Ví dụ: true, false
        'parent_id',        // Ví dụ: 1 (ID của danh mục cha, NULL nếu là danh mục gốc)
        'deleted_at',       // Trường xóa mềm
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getAllChildrenIds()
    {
        $ids = [$this->id];
        foreach($this->children as $child) {
            $ids = array_merge($ids, $child->getAllChildrenIds());
        }
        return $ids;
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class, 'category_id');
    }
}
