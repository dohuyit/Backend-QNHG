<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Dish;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $dishes = Dish::pluck('id')->toArray();
        foreach (Order::all() as $order) {
            $count = rand(1, 5);
            for ($i = 0; $i < $count; $i++) {
                $dishId = fake()->randomElement($dishes);
                OrderItem::create([
                    'order_id' => $order->id,
                    'dish_id' => $dishId,
                    'combo_id' => null,
                    'quantity' => rand(1, 3),
                    'unit_price' => fake()->randomFloat(2, 20, 100),
                    'notes' => fake()->boolean(30) ? fake()->sentence() : null,
                    'kitchen_status' => fake()->randomElement(['pending', 'preparing', 'ready', 'served', 'cancelled']),
                    'is_priority' => fake()->boolean(20),
                ]);
            }
        }
    }
}
