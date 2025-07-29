<?php
namespace App\Services\Notifications;

use App\Repositories\Notifications\NotificationRepositoryInterface;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected NotificationRepositoryInterface $notificationRepository;

    public function __construct(NotificationRepositoryInterface $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getListNotification(?int $receiverId = null, int $limit = 10)
    {
        return $this->notificationRepository->getListNotification($receiverId, $limit);
    }

    /**
     * Tạo thông báo cho việc cập nhật trạng thái đơn bếp
     *
     * @param array $kitchenOrderData Dữ liệu đơn bếp
     * @param string $previousStatus Trạng thái trước đó
     * @param string $newStatus Trạng thái mới
     * @return void
     */
    public function createKitchenOrderStatusNotification(array $kitchenOrderData, string $previousStatus, string $newStatus): void
    {
        // Lấy danh sách admin từ bảng users, user_roles, roles
        $admins = User::select('users.id')
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->where('roles.role_name', 'Admin')
            ->get();

        $statusMessages = [
            'pending' => 'chờ xử lý',
            'preparing' => 'đang chế biến',
            'ready' => 'sẵn sàng',
            'cancelled' => 'đã hủy'
        ];

        $newStatusText = $statusMessages[$newStatus] ?? $newStatus;

        foreach ($admins as $admin) {
            if (empty($previousStatus)) {
                // Trường hợp tạo đơn bếp mới
                $message = "Món '{$kitchenOrderData['item_name']}' (Số lượng: {$kitchenOrderData['quantity']}) vừa được tạo với trạng thái '{$newStatusText}'.";
            } else {
                // Trường hợp cập nhật trạng thái
                $previousStatusText = $statusMessages[$previousStatus] ?? $previousStatus;
                $message = "Món '{$kitchenOrderData['item_name']}' (Số lượng: {$kitchenOrderData['quantity']}) đã chuyển từ trạng thái '{$previousStatusText}' sang '{$newStatusText}'.";
            }

            Notification::create([
                'title' => empty($previousStatus) ? 'Đơn bếp mới' : 'Cập nhật trạng thái đơn bếp',
                'message' => $message,
                'type' => 'kitchen',
                'kitchen_order_id' => $kitchenOrderData['id'],
                'order_id' => $kitchenOrderData['order_id'] ?? null,
                'bill_id' => null,
                'receiver_id' => $admin->id,
                'read_at' => null,
            ]);
        }
    }

    /**
     * Tạo thông báo cho đơn hàng mới
     *
     * @param array $orderData Dữ liệu đơn hàng
     * @return void
     */
    public function createOrderNotification(array $orderData): void
    {
        try {
            // Lấy danh sách admin từ bảng users, user_roles, roles
            $admins = User::select('users.id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.role_name', 'Admin')
                ->get();

            Log::info('Creating order notification', [
                'order_data' => $orderData,
                'admin_count' => $admins->count()
            ]);

            if ($admins->isEmpty()) {
                Log::warning('No admin users found for order notification');
                return;
            }

            foreach ($admins as $admin) {
                $notification = Notification::create([
                    'title' => 'Đơn hàng mới',
                    'message' => "Đơn hàng {$orderData['order_code']} vừa được tạo với tổng tiền " . number_format($orderData['total_amount']) . " VNĐ.",
                    'type' => 'order',
                    'order_id' => $orderData['id'],
                    'reservation_id' => $orderData['reservation_id'] ?? null,
                    'bill_id' => null,
                    'kitchen_order_id' => null,
                    'receiver_id' => $admin->id,
                    'read_at' => null,
                ]);

                Log::info('Order notification created', [
                    'notification_id' => $notification->id,
                    'receiver_id' => $admin->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating order notification', [
                'error' => $e->getMessage(),
                'order_data' => $orderData
            ]);
        }
    }

    /**
     * Tạo thông báo cho việc cập nhật đơn hàng
     *
     * @param array $orderData Dữ liệu đơn hàng
     * @param string $previousStatus Trạng thái trước đó
     * @param string $newStatus Trạng thái mới
     * @return void
     */
    public function createOrderStatusNotification(array $orderData, string $previousStatus, string $newStatus): void
    {
        try {
            // Lấy danh sách admin từ bảng users, user_roles, roles
            $admins = User::select('users.id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.role_name', 'Admin')
                ->get();

            Log::info('Creating order status notification', [
                'order_data' => $orderData,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'admin_count' => $admins->count()
            ]);

            if ($admins->isEmpty()) {
                Log::warning('No admin users found for order status notification');
                return;
            }

            $statusMessages = [
                'pending_confirmation' => 'chờ xác nhận',
                'confirmed' => 'đã xác nhận',
                'preparing' => 'đang chuẩn bị',
                'ready_to_serve' => 'sẵn sàng phục vụ',
                'served' => 'đã phục vụ',
                'ready_for_pickup' => 'sẵn sàng nhận',
                'delivering' => 'đang giao',
                'completed' => 'hoàn thành',
                'cancelled' => 'đã hủy',
                'payment_failed' => 'thanh toán thất bại'
            ];

            $previousStatusText = $statusMessages[$previousStatus] ?? $previousStatus;
            $newStatusText = $statusMessages[$newStatus] ?? $newStatus;

            foreach ($admins as $admin) {
                $notification = Notification::create([
                    'title' => 'Cập nhật trạng thái đơn hàng',
                    'message' => "Đơn hàng {$orderData['order_code']} đã chuyển từ trạng thái '{$previousStatusText}' sang '{$newStatusText}'.",
                    'type' => 'order',
                    'order_id' => $orderData['id'],
                    'reservation_id' => $orderData['reservation_id'] ?? null,
                    'bill_id' => null,
                    'kitchen_order_id' => null,
                    'receiver_id' => $admin->id,
                    'read_at' => null,
                ]);

                Log::info('Order status notification created', [
                    'notification_id' => $notification->id,
                    'receiver_id' => $admin->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error creating order status notification', [
                'error' => $e->getMessage(),
                'order_data' => $orderData,
                'previous_status' => $previousStatus,
                'new_status' => $newStatus
            ]);
        }
    }
}
