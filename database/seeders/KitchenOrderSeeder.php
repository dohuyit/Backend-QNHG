<?php

namespace Database\Seeders;

use App\Models\KitchenOrder;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class KitchenOrderSeeder extends Seeder
{
    public function run(): void
    {
        // Các giá trị hợp lệ cho trường status theo enum trong DB
        $validStatuses = ['pending', 'preparing', 'ready', 'cancelled'];

        $availableOrderItems = OrderItem::doesntHave('kitchenOrder')->with('order', 'menuItem')->get();

        foreach ($availableOrderItems as $item) {
            if (!$item->order || !$item->menuItem) {
                continue;
            }

            // Lấy status từ order item, nếu không hợp lệ thì mặc định là 'pending'
            $status = in_array($item->kitchen_status, $validStatuses) ? $item->kitchen_status : 'pending';

            KitchenOrder::create([
                'order_item_id' => $item->id,
                'order_id' => $item->order_id,
                'table_number' => $item->order->table_id,
                'item_name' => $item->menuItem->name,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
                'status' => $status,
                'is_priority' => $item->is_priority,
                'received_at' => now()->subMinutes(rand(1, 20)),
                'completed_at' => null,
            ]);
        }
    }
}
