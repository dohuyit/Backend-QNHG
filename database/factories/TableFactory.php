<?php

namespace Database\Factories;

use App\Models\Table;
use App\Models\TableArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    protected $model = Table::class;

    public function definition(): array
    {
        return [
            'table_area_id' => TableArea::factory(), // hoặc chỉ định cụ thể ID nếu cần
            'table_number' => strtoupper($this->faker->bothify('??##')), // VD: "A10"
            'table_type' => $this->faker->randomElement(['2_seats', '4_seats', '8_seats']),
            'description' => $this->faker->sentence(),
            'tags' => $this->faker->randomElements(['yên tĩnh', 'view đẹp', 'ghế sofa', 'gần cửa sổ', 'riêng tư'], rand(1, 3)),
            'status' => $this->faker->optional(0.3)->randomElement(['available', 'occupied', 'cleaning', 'out_of_service']), // 30% có status, 70% null
        ];
    }
}
