<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Models\Table;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $orderTypes = ['dine-in', 'takeaway', 'delivery'];
        $type = $this->faker->randomElement($orderTypes);

        return [
            'order_code' => strtoupper(Str::random(8)),
            'order_type' => $type,
            'table_id' => $type === 'dine-in' ? Table::inRandomOrder()->first()?->id : null,
            'reservation_id' => null, // Có thể cập nhật sau nếu có model Reservation
            'user_id' => User::inRandomOrder()->first()?->id,
            'customer_id' => Customer::inRandomOrder()->first()?->id,
            'order_time' => $this->faker->dateTimeBetween('-2 days', 'now'),
            'status' => $this->faker->randomElement(['pending_confirmation', 'confirmed', 'preparing', 'served', 'completed']),
            'payment_status' => $this->faker->randomElement(['unpaid', 'partially_paid', 'paid']),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'delivery_address' => $type === 'delivery' ? $this->faker->address() : null,
            'delivery_contact_name' => $type === 'delivery' ? $this->faker->name() : null,
            'delivery_contact_phone' => $type === 'delivery' ? $this->faker->phoneNumber() : null,
            'total_amount' => $this->faker->randomFloat(2, 50, 300),
            'final_amount' => function (array $attributes) {
                return $attributes['total_amount'] - rand(0, 30); // giả sử có giảm giá
            },
            'delivered_at' => $type === 'delivery' ? $this->faker->dateTimeBetween('+1 hour', '+2 hours') : null,
        ];
    }
}
