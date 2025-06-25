<?php

namespace Database\Seeders;

use App\Models\Dish;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'description' => 'Rượu mơ 9chum được làm từ mơ tươi, hương vị thơm ngon, nồng độ vừa phải.',
                'image_url' => null,
                'status' => 'active',
                'is_featured' => true,
                'category_id' => 8,
                'original_price' => 120000.00,
                'selling_price' => 100000.00,
                'tags' => json_encode(['best-seller', 'đồ uống']),
                'unit' => 'glass',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nước cam ép 320ml',
                'description' => 'Nước cam ép tươi mát, giàu vitamin C, giải khát tuyệt vời.',
                'image_url' => null,
                'status' => 'active',
                'is_featured' => false,
                'category_id' => 10,
                'original_price' => 35000.00,
                'selling_price' => 30000.00,
                'tags' => json_encode(['best-seller', 'đồ uống']),
                'unit' => 'glass',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Bia Heineken',
                'description' => 'Bia Heineken mát lạnh, hương vị đặc trưng, thích hợp cho các bữa tiệc.',
                'image_url' => null,
                'status' => 'inactive',
                'is_featured' => false,
                'category_id' => 9,
                'original_price' => 45000.00,
                'selling_price' => 40000.00,
                'tags' => json_encode(['best-seller', 'đồ uống']),
                'unit' => 'glass',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Mực hấp sốt Thái',
                'description' => 'Mực tươi hấp với sốt Thái cay nồng, đậm đà hương vị biển.',
                'image_url' => null,
                'status' => 'active',
                'is_featured' => true,
                'category_id' => 12,
                'original_price' => 90000.00,
                'selling_price' => 85000.00,
                'tags' => json_encode(['best-seller', 'món nhậu', 'đặc sản']),
                'unit' => 'plate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Nộm dê rau má',
                'description' => 'Nộm dê rau má tươi ngon, kết hợp giữa thịt dê mềm và rau má thanh mát.',
                'image_url' => null,
                'status' => 'active',
                'is_featured' => false,
                'category_id' => 4,
                'original_price' => 70000.00,
                'selling_price' => 65000.00,
                'tags' => json_encode(['best-seller', 'đặc sản']),
                'unit' => 'plate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chân gà sốt Thái chua cay',
                'description' => 'Chân gà sốt Thái chua cay, giòn sần sật, hương vị đậm đà, thích hợp làm món nhậu.',
                'image_url' => null,
                'status' => 'active',
                'is_featured' => true,
                'category_id' => 13,
                'original_price' => 80000.00,
                'selling_price' => 75000.00,
                'tags' => json_encode(['best-seller', 'món nhậu', 'đặc sản']),
                'unit' => 'plate',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}