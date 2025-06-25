<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Insert các danh mục cha
        $parents = [
            'Thực đơn' => Category::create([
                'name' => 'Thực đơn',
                'description' => 'Thăng hoa vị giác với hơn 300 món đặc sắc từ nhà hàng Tự Do',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ]),
            'Cơ sở' => Category::create([
                'name' => 'Cơ sở',
                'description' => 'Danh sách các cơ sở của quán nhậu Tự Do',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ]),
            'Ưu đãi' => Category::create([
                'name' => 'Ưu đãi',
                'description' => 'Tổng hợp các chương trình khuyến mãi hấp dẫn',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ]),
            'Liên hệ' => Category::create([
                'name' => 'Liên hệ',
                'description' => 'Thông tin liên hệ với quán nhậu Tự Do',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ]),
        ];

        // Insert danh mục con thuộc Thực đơn
        $menuItems = [
            'Combo',
            'Món mới',
            'Đồ uống',
            'Dê tươi',
            'Salad - Nộm',
            'Rau xanh',
            'Thiết bàn',
            'Đồ nướng',
            'Hải sản',
            'Cá các món',
            'Món ăn chơi',
            'Món nhậu',
            'Lẩu',
            'Cơm - Mỳ',
        ];

        foreach ($menuItems as $name) {
            Category::create([
                'name' => $name,
                'description' => "Món $name hấp dẫn phục vụ trong thực đơn.",
                'image_url' => null,
                'is_active' => true,
                'parent_id' => $parents['Thực đơn']->id,
            ]);
        }
    }
}
