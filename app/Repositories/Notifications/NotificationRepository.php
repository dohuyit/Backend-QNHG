<?php
namespace App\Repositories\Notifications;

use App\Models\Notification;
use Illuminate\Support\Collection;

interface NotificationRepositoryInterface
{
    public function getListNotification(?int $receiverId = null, int $limit = 10): Collection;
}

class NotificationRepository implements NotificationRepositoryInterface
{
    /**
     * Lấy danh sách thông báo theo ID người nhận (nếu có), giới hạn số lượng.
     *
     * @param int|null $receiverId ID người nhận (có thể null để lấy tất cả)
     * @param int $limit Số lượng thông báo muốn lấy
     * @return Collection Danh sách thông báo
     */
    public function getListNotification(?int $receiverId = null, int $limit = 10): Collection
    {
        $query = Notification::orderBy('created_at', 'desc');
        if (!is_null($receiverId)) {
            $query->where('receiver_id', $receiverId);
        }
        return $query->limit($limit)->get();
    }

    /**
     * Tạo thông báo mới
     *
     * @param array $data Dữ liệu thông báo
     * @return Notification
     */
    public function createNotification(array $data): Notification
    {
        return Notification::create($data);
    }
}
