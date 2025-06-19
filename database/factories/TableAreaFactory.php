<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TableAreaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'Khu vực ' . $this->faker->unique()->word,
            'description' => "Mô tả cho khu vực",
            'status' => $this->faker->randomElement(['active', 'inactive']),
            'capacity' => $this->faker->numberBetween(5, 50),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
