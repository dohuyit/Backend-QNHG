<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use App\Models\Customer;
use App\Models\Table;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $orderTypes = ['dine-in', 'takeaway', 'delivery'];
        $type = $this->faker->randomElement($orderTypes);
        $status = $this->faker->randomElement([
            'pending_confirmation',
            'confirmed',
            'preparing',
            'ready_to_serve',
            'served',
            'ready_for_pickup',
            'delivering',
            'completed',
            'cancelled',
            'payment_failed'
        ]);

        $isDelivery = $type === 'delivery';
        $isCompletedOrDelivered = $isDelivery && in_array($status, ['completed', 'delivering']);
        $hasCustomer = $this->faker->boolean(70);
        $orderTime = $this->faker->dateTimeBetween('-7 days', 'now');
        $totalAmount = $this->faker->randomFloat(2, 50, 500);
        $discount = $this->faker->randomFloat(2, 0, $totalAmount * 0.2);
        $finalAmount = max(0, $totalAmount - $discount);
        $createdAt = $this->faker->dateTimeBetween('-7 days', 'now');
        $updatedAt = $this->faker->dateTimeBetween($createdAt, 'now');

        return [
            'order_code' => 'ORD-' . strtoupper(Str::random(10)),
            'order_type' => $type,
            'table_id' => $type === 'dine-in' ? Table::inRandomOrder()->first()?->id : null,
            'reservation_id' => $this->faker->boolean(30) ? Reservation::inRandomOrder()->first()?->id : null,
            'user_id' => User::inRandomOrder()->first()?->id,
            'customer_id' => $hasCustomer ? Customer::inRandomOrder()->first()?->id : null,
            'order_time' => $orderTime,
            'status' => $status,
            'payment_status' => $this->faker->randomElement(['unpaid', 'partially_paid', 'paid', 'refunded']),
            'notes' => $this->faker->boolean(30) ? $this->faker->sentence() : null,
            'delivery_address' => $isDelivery ? $this->faker->address() : null,
            'contact_name' => !$hasCustomer || $isDelivery ? $this->faker->name() : null,
            'contact_email' => !$hasCustomer || $isDelivery ? $this->faker->safeEmail() : null,
            'contact_phone' => !$hasCustomer || $isDelivery ? $this->faker->phoneNumber() : null,
            'total_amount' => $totalAmount,
            'final_amount' => $finalAmount,
            'delivered_at' => $isCompletedOrDelivered ? $this->faker->dateTimeBetween($orderTime, 'now') : null,
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
        ];
    }
}
