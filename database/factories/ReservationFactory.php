<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    public function definition(): array
    {
        $table = Table::inRandomOrder()->first();

        return [
            'customer_id' => Customer::factory(),
            'customer_name' => $this->faker->name,
            'customer_phone' => $this->faker->phoneNumber,
            'customer_email' => $this->faker->safeEmail,
            'reservation_date' => $this->faker->dateTimeBetween('+1 days', '+15 days')->format('Y-m-d'),
            'reservation_time' => $this->faker->dateTimeBetween('09:00', '21:00')->format('H:i'),
            'number_of_guests' => rand(1, 10),
            'table_id' => $table->id,
            'notes' => $this->faker->optional()->sentence,
            'status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled', 'completed']),
            'user_id' => User::factory(),
            'confirmed_at' => $this->faker->optional()->dateTimeBetween('-1 days', 'now'),
            'cancelled_at' => null,
            'completed_at' => null,
        ];
    }
}
