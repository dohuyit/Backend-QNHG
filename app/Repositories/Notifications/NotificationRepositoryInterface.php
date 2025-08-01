<?php
namespace App\Repositories\Notifications;

use Illuminate\Support\Collection;
use App\Models\Notification;

interface NotificationRepositoryInterface
{
    /**
     * Lấy danh sách thông báo theo ID người nhận (nếu có), giới hạn số lượng.
     *
     * @param int|null $receiverId ID người nhận (có thể null để lấy tất cả)
     * @param int $limit Số lượng thông báo muốn lấy
     * @return Collection Danh sách thông báo
     */
    public function getListNotification(?int $receiverId = null, int $limit = 10): Collection;

    /**
     * Tạo thông báo mới
     *
     * @param array $data Dữ liệu thông báo
     * @return Notification
     */
    public function createNotification(array $data): Notification;
}
