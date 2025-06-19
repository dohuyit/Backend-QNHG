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
            'capacity' => $this->faker->numberBetween(2, 10),
            'min_guests' => 2,
            'max_guests' => $this->faker->numberBetween(4, 12),
            'description' => $this->faker->sentence(),
            'tags' => $this->faker->randomElements(['yên tĩnh', 'view đẹp', 'ghế sofa', 'gần cửa sổ', 'riêng tư'], rand(1, 3)),
            'status' => $this->faker->randomElement(['available', 'occupied', 'reserved', 'cleaning', 'out_of_service']),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
