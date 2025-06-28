<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Table;
use App\Models\User;
use App\Models\Reservation;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::pluck('id')->toArray();
        $tables = Table::pluck('id')->toArray();
        $users = User::pluck('id')->toArray();
        $reservations = Reservation::pluck('id')->toArray();

        for ($i = 0; $i < 30; $i++) {
            $type = fake()->randomElement(['dine-in', 'takeaway', 'delivery']);
            $hasCustomer = fake()->boolean(70);
            $order = Order::create([
                'order_code' => 'ORD-' . strtoupper(fake()->unique()->bothify('##########')),
                'order_type' => $type,
                'table_id' => $type === 'dine-in' ? fake()->randomElement($tables) : null,
                'reservation_id' => fake()->boolean(30) ? (count($reservations) ? fake()->randomElement($reservations) : null) : null,
                'user_id' => count($users) ? fake()->randomElement($users) : null,
                'customer_id' => $hasCustomer && count($customers) ? fake()->randomElement($customers) : null,
                'order_time' => now()->subMinutes(rand(1, 1000)),
                'status' => fake()->randomElement(['pending_confirmation','confirmed','preparing','ready_to_serve','served','ready_for_pickup','delivering','completed','cancelled','payment_failed']),
                'payment_status' => fake()->randomElement(['unpaid', 'partially_paid', 'paid', 'refunded']),
                'notes' => fake()->boolean(30) ? fake()->sentence() : null,
                'delivery_address' => $type === 'delivery' ? fake()->address() : null,
                'contact_name' => !$hasCustomer || $type === 'delivery' ? fake()->name() : null,
                'contact_email' => !$hasCustomer || $type === 'delivery' ? fake()->safeEmail() : null,
                'contact_phone' => !$hasCustomer || $type === 'delivery' ? fake()->phoneNumber() : null,
                'total_amount' => fake()->randomFloat(2, 50, 500),
                'final_amount' => fake()->randomFloat(2, 50, 500),
                'delivered_at' => null,
            ]);
        }
    }
}
