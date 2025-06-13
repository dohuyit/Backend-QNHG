<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Table;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    
    public function definition(): array
    {
        return [
            'customer_id' => Customer::inRandomOrder()->value('id'),
            'customer_name' => fake()->name,
            'customer_phone' => fake()->phoneNumber,
            'customer_email' => fake()->safeEmail,
            'reservation_time' => fake()->dateTimeBetween('+1 days', '+15 days'),
            'number_of_guests' => rand(1, 10),
            'table_id' => Table::inRandomOrder()->value('id'),
            'notes' => fake()->optional()->sentence,
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled', 'completed', 'no_show', 'seated']),
            'user_id' => rand(1,3),
            'confirmed_at' => fake()->optional()->dateTimeBetween('-1 days', 'now'),
            'cancelled_at' => null,
            'completed_at' => null,
        ];
    }
}
