<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            [
                'name' => 'Đồ uống',
                'slug' => Str::slug('Đồ uống'),
                'description' => 'Thức uống đa dạng: nước ngọt, bia lạnh, rượu ngon – giải khát tuyệt vời cho mọi bữa ăn.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Món nhậu',
                'slug' => Str::slug('Món nhậu'),
                'description' => 'Đậm vị dân chơi: các món nhắm chất lượng, lý tưởng cho những cuộc vui tới bến.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Hải sản',
                'slug' => Str::slug('Hải sản'),
                'description' => 'Tươi sống từ biển: tôm, cua, mực, cá chế biến theo kiểu nhà hàng, giữ trọn vị ngọt tự nhiên.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Salad - Nộm',
                'slug' => Str::slug('Salad - Nộm'),
                'description' => 'Món ăn thanh đạm, rau củ tươi sạch',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
        ]);
    }
}
