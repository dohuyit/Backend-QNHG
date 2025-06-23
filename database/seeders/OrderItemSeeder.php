<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        // Với mỗi đơn hàng, tạo 1–5 món
        foreach (Order::all() as $order) {
            OrderItem::factory()
                ->count(rand(1, 5))
                ->create([
                    'order_id' => $order->id,
                ]);
        }
    }
}
