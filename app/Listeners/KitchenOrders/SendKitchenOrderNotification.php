<?php

namespace App\Listeners\KitchenOrders;

use App\Events\KitchenOrders\KitchenOrderCreated;
use App\Models\Notification;
use App\Models\User;

class SendKitchenOrderNotification
{
    public function handle(KitchenOrderCreated $event): void
    {
        $order = $event->kitchenOrder;
        // Lấy danh sách admin
        $admins = User::whereHas('roles', function ($q) {
            $q->where('role_name', 'Admin');
        })->get();

        foreach ($admins as $admin) {
            Notification::create([
                'title' => 'Đơn bếp mới',
                'message' => "Món {$order['item_name']} vừa thêm vào bếp.",
                'type' => 'kitchen_order',
                'kitchen_order_id' => $order['id'] ?? null,
                'order_id' => $order['order_id'] ?? null,
                'receiver_id' => $admin->id,
            ]);
        }
    }
}
