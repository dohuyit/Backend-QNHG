<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderCreated;
use App\Models\Notification;
use App\Models\User;

class SendOrderNotification
{
    public function handle(OrderCreated $event)
    {
        $order = $event->order;
        // Lấy danh sách admin (hoặc user nhận thông báo)
        $admins = User::whereHas('roles', function ($q) {
            $q->where('role_name', 'Admin'); // Phân biệt hoa thường!
        })->get();
        foreach ($admins as $admin) {
            Notification::create([
                'title' => 'Đơn hàng mới',
                'message' => "Đơn hàng #{$order['order_code']} vừa được tạo từ xác nhận đặt bàn.",
                'type' => 'order',
                'order_id' => $order['id'] ?? null,
                'receiver_id' => $admin->id,
            ]);
        }
    }
}