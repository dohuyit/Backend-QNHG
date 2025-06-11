<?php

namespace Database\Seeders;

use App\Models\Combo;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComboSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Combo::insert([
            [
                'name' => 'Bàn Tiệc Tự Do',
                'description' => 'Thỏa sức lựa chọn các món ăn yêu thích từ thực đơn đa dạng của chúng tôi.',
                'original_total_price' => 1200000.00,
                'selling_price' => 999000.00,
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
            ],
            [
                'name' => 'Gia Đình Vui Vẻ',
                'description' => 'Combo hoàn hảo cho gia đình với các món ăn ngon miệng và tiết kiệm.',
                'original_total_price' => 800000.00,
                'selling_price' => 699000.00,
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
            ],
            [
                'name' => 'Gắp Là Dính',
                'description' => 'Thỏa sức lựa chọn các món ăn yêu thích từ thực đơn đa dạng của chúng tôi.',
                'original_total_price' => 1200000.00,
                'selling_price' => 999000.00,
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
            ],
            [
                'name' => 'Lẩu Thái Đặc Biệt',
                'description' => 'Combo lẩu với nước dùng đậm đà, hải sản tươi ngon và rau củ tươi mát.',
                'original_total_price' => 600000.00,
                'selling_price' => 499000.00,
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
            ],
        ]);
    }
}
