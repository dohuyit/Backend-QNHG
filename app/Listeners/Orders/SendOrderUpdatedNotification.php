<?php

namespace App\Listeners\Orders;

use App\Events\Orders\OrderUpdated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderUpdatedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(OrderUpdated $event)
    {
        try {
            $order = $event->order;
            
            Log::info('Creating order updated notification from listener', [
                'order_data' => $order
            ]);

            // Lấy danh sách admin
            $admins = User::select('users.id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.role_name', 'Admin')
                ->get();

            if ($admins->isEmpty()) {
                Log::warning('No admin users found for order updated notification');
                return;
            }

            foreach ($admins as $admin) {
                $notification = Notification::create([
                    'title' => 'Cập nhật đơn hàng',
                    'message' => "Đơn hàng {$order['order_code']} vừa được cập nhật.",
                    'type' => 'order',
                    'order_id' => $order['id'] ?? null,
                    'reservation_id' => null,
                    'bill_id' => null,
                    'kitchen_order_id' => null,
                    'receiver_id' => $admin->id,
                    'read_at' => null,
                ]);

                Log::info('Order updated notification created from listener', [
                    'notification_id' => $notification->id,
                    'receiver_id' => $admin->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating order updated notification from listener', [
                'error' => $e->getMessage(),
                'order_data' => $event->order
            ]);
        }
    }
}
