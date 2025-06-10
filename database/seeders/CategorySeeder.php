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
                'description' => 'Thức uống đa dạng: nước ngọt, bia lạnh, rượu ngon – giải khát tuyệt vời cho mọi bữa ăn.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Món nhậu',
                'description' => 'Đậm vị dân chơi, các món nhắm chất lượng, lý tưởng cho những cuộc vui tới bến.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Hải sản',
                'description' => 'Tươi sống từ biển: tôm, cua, mực, cá chế biến theo kiểu nhà hàng, giữ trọn vị ngọt tự nhiên.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Salad - Nộm',
                'description' => 'Món ăn thanh đạm, rau củ tươi sạch',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Đồ nướng',
                'description' => 'Thưởng thức các món nướng thơm lừng, được tẩm ướp công phu và chế biến trên than hồng chuẩn vị',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Lẩu',
                'description' => 'Đa dạng hương vị lẩu đặc sắc, từ thanh ngọt đến cay nồng, phù hợp cho mọi dịp sum họp và tiệc tùng',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Cơm - Mỳ',
                'description' => 'Tổng hợp các món cơm và mì được chế biến kỹ lưỡng, cân bằng dinh dưỡng và phù hợp khẩu vị',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => null,
            ],
        ]);

        Category::insert([
            [
                'name' => 'Rượu',
                'description' => 'Thức uống đa dạng: nước ngọt, bia lạnh, rượu ngon – giải khát tuyệt vời cho mọi bữa ăn.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => 1,
            ],
               [
                'name' => 'Bia',
                'description' => 'Thức uống đa dạng: nước ngọt, bia lạnh, rượu ngon – giải khát tuyệt vời cho mọi bữa ăn.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => 1,
            ],
            [
                'name' => 'Nước',
                'description' => 'Thức uống đa dạng: nước ngọt, bia lạnh, rượu ngon – giải khát tuyệt vời cho mọi bữa ăn.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => 1,
            ],
            [
                'name' => 'Tôm',
                'description' => 'Tươi sống từ biển: tôm, cua, mực, cá chế biến theo kiểu nhà hàng, giữ trọn vị ngọt tự nhiên.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => 3,
            ],
            [
                'name' => 'Mực',
                'description' => 'Tươi sống từ biển: tôm, cua, mực, cá chế biến theo kiểu nhà hàng, giữ trọn vị ngọt tự nhiên.',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => 3,
            ],
            [
                'name' => 'Gà',
                'description' => '',
                'image_url' => fake()->imageUrl(),
                'is_active' => true,
                'parent_id' => 2,
            ],
        ]);
    }
}
