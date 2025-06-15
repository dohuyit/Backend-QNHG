<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTable;
use App\Models\OrderItemChangeLog;
use App\Models\User;
use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Table;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo dữ liệu mẫu cho đơn hàng
        $orderTypes = ['dine-in', 'takeaway', 'delivery'];
        $statuses = ['pending_confirmation', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled'];
        $paymentStatuses = ['unpaid', 'partially_paid', 'paid', 'refunded'];
        $kitchenStatuses = ['pending', 'preparing', 'ready', 'served', 'cancelled'];

        // Lấy một số dữ liệu cần thiết
        $users = User::all();
        $customers = Customer::all();
        $tables = Table::all();

        // Tạo 20 đơn hàng mẫu
        for ($i = 1; $i <= 20; $i++) {
            $orderType = $orderTypes[array_rand($orderTypes)];
            $order = Order::create([
                'order_code' => 'ORD' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'order_type' => $orderType,
                'user_id' => $users->random()->id,
                'customer_id' => $customers->random()->id,
                'order_time' => Carbon::now()->subHours(rand(1, 24)),
                'status' => $statuses[array_rand($statuses)],
                'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
                'notes' => 'Ghi chú cho đơn hàng #' . $i,
                'delivery_address' => $orderType === 'delivery' ? 'Địa chỉ giao hàng #' . $i : null,
                'delivery_contact_name' => $orderType === 'delivery' ? 'Người nhận #' . $i : null,
                'delivery_contact_phone' => $orderType === 'delivery' ? '0123456789' : null,
                'total_amount' => rand(100000, 1000000),
                'final_amount' => rand(100000, 1000000),
                'preparation_time_estimated_minutes' => rand(15, 60),
                'preparation_time_actual_minutes' => rand(15, 60),
            ]);

            // Nếu là đơn tại bàn, thêm thông tin bàn
            if ($orderType === 'dine-in') {
                OrderTable::create([
                    'order_id' => $order->id,
                    'table_id' => $tables->random()->id,
                    'notes' => 'Ghi chú bàn cho đơn hàng #' . $i
                ]);
            }

            // Tạo 2-5 món cho mỗi đơn hàng
            $numItems = rand(2, 5);
            for ($j = 0; $j < $numItems; $j++) {
                $quantity = rand(1, 3);
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'quantity' => $quantity,
                    'notes' => 'Ghi chú cho món #' . ($j + 1),
                    'kitchen_status' => $kitchenStatuses[array_rand($kitchenStatuses)],
                    'is_priority' => rand(0, 1)
                ]);

                // Tạo log thay đổi cho mỗi món
                OrderItemChangeLog::create([
                    'order_item_id' => $orderItem->id,
                    'order_id' => $order->id,
                    'user_id' => $users->random()->id,
                    'change_timestamp' => Carbon::now(),
                    'change_type' => 'STATUS_UPDATE',
                    'field_changed' => 'kitchen_status',
                    'old_value' => 'pending',
                    'new_value' => $orderItem->kitchen_status,
                    'reason' => 'Cập nhật trạng thái món'
                ]);
            }
        }
    }
}
