<?php

namespace Database\Seeders;

use App\Models\Bill;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BillSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::limit(10)->get(); // Lấy 10 đơn hàng đầu tiên

        foreach ($orders as $order) {
            $subTotal = $order->total_amount;
            $discount = fake()->randomFloat(2, 0, $subTotal * 0.3); // Giảm giá từ 0–30%
            $delivery = fake()->randomFloat(2, 0, 50000); // Phí ship từ 0–50k
            $finalAmount = $subTotal - $discount + $delivery;

            Bill::create([
                'order_id'       => $order->id,
                'bill_code'      => 'HD-' . strtoupper(Str::random(8)),
                'sub_total'      => $subTotal,
                'discount_amount'=> $discount,
                'delivery_fee'   => $delivery,
                'final_amount'   => $finalAmount,
                'status'         => fake()->randomElement(['unpaid', 'paid']),
                'issued_at'      => now()->subDays(rand(0, 30)),
                'user_id'        => User::inRandomOrder()->value('id'),
            ]);
        }
    }
}
