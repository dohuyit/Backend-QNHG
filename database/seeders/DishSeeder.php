<?php

namespace Database\Seeders;

use App\Models\Dish;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DishSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Dish::insert([
            [
                'name' => 'Rượu mơ 9chum (500ml)',
                'slug' => Str::slug('Rượu mơ 9chum (500ml)'),
                'description' => 'Rượu mơ 9chum được làm từ mơ tươi, hương vị thơm ngon, nồng độ vừa phải.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'category_id' => 8,
                'original_price' => 120000,
                'selling_price' => 100000,
                'tags' => json_encode(["best-seller","đồ uống"]),
                'unit' => 'Chai'
            ],
            [
                'name' => 'Nước cam ép 320ml',
                'slug' => Str::slug('Nước cam ép 320ml'),
                'description' => 'Nước cam ép tươi mát, giàu vitamin C, giải khát tuyệt vời.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'category_id' => 10,
                'original_price' => 35000,
                'selling_price' => 30000,
                'tags' => json_encode(["best-seller", "đồ uống"]),
                'unit' => 'Chai'
            ],
            [
                'name' => 'Bia Heiniken',
                'slug' => Str::slug('Bia Heiniken'),
                'description' => 'Bia Heiniken mát lạnh, hương vị đặc trưng, thích hợp cho các bữa tiệc.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'category_id' => 9,
                'original_price' => 45000,
                'selling_price' => 40000,
                'tags' => json_encode(["best-seller", "đồ uống"]),
                'unit' => 'Chai'
            ],
            [
                'name' => 'Mực hấp sốt Thái',
                'slug' => Str::slug('Mực hấp sốt Thái'),
                'description' => 'Mực tươi hấp với sốt Thái cay nồng, đậm đà hương vị biển.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'category_id' => 12,
                'original_price' => 90000,
                'selling_price' => 85000,
                'tags' => json_encode(["best-seller", "món nhậu", "đặc sản"]),
                'unit' => 'Đĩa'
            ],
            [
                'name' => 'Nộm dê rau má',
                'slug' => Str::slug('Nộm dê rau má'),
                'description' => 'Nộm dê rau má tươi ngon, kết hợp giữa thịt dê mềm và rau má thanh mát.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'category_id' => 4,
                'original_price' => 70000,
                'selling_price' => 65000,
                'tags' => json_encode(["best-seller", "món ăn đặc sản"]),
                'unit' => 'Đĩa'
            ],
            [
                'name' => 'Chân gà sốt Thái chua cay',
                'slug' => Str::slug('Chân gà sốt Thái chua cay'),
                'description' => 'Chân gà sốt Thái chua cay, giòn sần sật, hương vị đậm đà, thích hợp làm món nhậu.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'category_id' => 13,
                'original_price' => 80000,
                'selling_price' => 75000,
                'tags' => json_encode(["best-seller", "món nhậu,đặc sản"]),
                'unit' => 'Đĩa'

            ],
        ]);
    }
}
