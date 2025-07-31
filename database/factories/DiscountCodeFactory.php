<?php

namespace Database\Factories;

use App\Models\DiscountCode;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountCodeFactory extends Factory
{
    protected $model = DiscountCode::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed']);
        $value = $type === 'percentage'
            ? $this->faker->numberBetween(5, 20)
            : $this->faker->randomFloat(2, 10000, 50000); // giảm 10k–50k 

        $codeSamples = [
            'NHAUHOANGGIA', 'VUITET2026', 'SIEUGIAMGIA', 'BIAFREE', 'MUNGKHAITRUONG',
            'TETCUNGHOANGGIA', 'TRUNGTHUNHAU', 'GIAM30415', 'HALLOWBEER ', 'NHAUFREE',
            'GIAMGIAVOIVANG', 'GIAM50KHOANGIA', 'KHAIBIA', 'BEERFEST2025', 'CHILL10K',
        ];

        return [
            'code' => $this->faker->unique()->randomElement($codeSamples),
            'type' => $type,
            'value' => $value,
            'start_date' => now(),
            'end_date' => now()->addDays(rand(7, 30)),
            'usage_limit' => rand(20, 200),
            'used' => rand(0, 10),
            'is_active' => true,
        ];
    }
}
