<?php

namespace App\Services\Reservations;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Events\Reservations\ReservationCreated;
use App\Events\Reservations\ReservationStatusUpdated;
use App\Models\Reservation;
use App\Repositories\Reservations\ReservationRepositoryInterface;
use App\Repositories\Table\TableRepositoryInterface;
use App\Services\Mails\ReservationMailService;
use App\Services\Order\OrderService;

class ReservationService
{
    protected ReservationRepositoryInterface $reservationRepository;
    protected TableRepositoryInterface $tableRepository;
    protected ReservationMailService  $reservationMailService;
    protected OrderService $orderService;
    public function __construct(ReservationRepositoryInterface $reservationRepository, TableRepositoryInterface $tableRepository, ReservationMailService $reservationMailService, OrderService $orderService)
    {
        $this->reservationRepository = $reservationRepository;
        $this->tableRepository = $tableRepository;
        $this->reservationMailService = $reservationMailService;
        $this->orderService = $orderService;
    }
    public function getListReservation(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 8;

        $pagination = $this->reservationRepository->getReservationList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'customer_id' => $item->customer_id,
                'customer_name' => $item->customer_name,
                'customer_phone' => $item->customer_phone,
                'customer_email' => $item->customer_email,
                'reservation_date' => $item->reservation_date,
                'reservation_time' => $item->reservation_time,
                'number_of_guests' => $item->number_of_guests,
                'notes' => $item->notes,
                'status' => $item->status,
                'user_id' => $item->user_id,
                'confirmed_at' => $item->confirmed_at,
                'cancelled_at' => $item->cancelled_at,
                'completed_at' => $item->completed_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }
    public function createReservation(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $listDataCreate = [
            'customer_id' => $data['customer_id'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'],
            'reservation_date' => $data['reservation_date'],
            'reservation_time' => $data['reservation_time'],
            'number_of_guests' => $data['number_of_guests'],
            'notes' => $data['notes'],
            'status' => $data['status'] ?? 'pending',
            'user_id' => $data['user_id'],
        ];

        $ok  = $this->reservationRepository->createData($listDataCreate);
        if ($ok) {
            $this->reservationRepository->createReservationChangeLog([
                'reservation_id' => $ok->id,
                'user_id' => $ok->user_id,
                'change_timestamp' => now(),
                'change_type' => 'CREATE',
                'field_changed' => null,
                'old_value' => null,
                'new_value' => null,
                'description' => 'Tạo mới đơn đặt bàn',
            ]);
        }
        if (!$ok) {
            $result->setMessage(message: 'Tạo đơn đặt bàn thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Tạo đơn đặt bàn thành công!');



        event(new ReservationCreated($listDataCreate));

        $this->reservationMailService->sendClientConfirmMail($listDataCreate);

        return $result;
    }
    public function getReservationDetail(int $id): DataAggregate
    {
        $result = new DataAggregate();

        $reservation  = $this->reservationRepository->getByConditions(['id' => $id]);
        if (!$reservation) {
            $result = new DataAggregate();
            $result->setResultError(message: 'Đơn đặt bàn không tồn tại');
            return $result;
        }
        // Lấy lịch sử thay đổi
        $changeLogs = $this->reservationRepository->getReservationChangeLogs($id);

        $result->setResultSuccess(data: [
            'reservation' => $reservation,
            'change_logs' => $changeLogs,
        ]);
        return $result;
    }
    public function updateReservation(array $data, Reservation $reservation): DataAggregate
    {
        $result = new DataAggregate();

        $listDataUpdate = [
            'customer_id' => $data['customer_id'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'],
            'reservation_time' => $data['reservation_time'],
            'reservation_date' => $data['reservation_date'],
            'number_of_guests' => $data['number_of_guests'],
            // Nếu muốn giữ table_id trong bảng reservations thì lấy bàn đầu tiên, còn không thì bỏ dòng này
            'table_id' => is_array($data['table_id']) ? ($data['table_id'][0] ?? null) : $data['table_id'],
            'notes' => $data['notes'],
            'status' => $data['status'] ?? $reservation->status,
            'user_id' => $data['user_id'],
        ];

        $newStatus = $listDataUpdate['status'];
        $currentStatus = $reservation->status;

        if ($newStatus !== $currentStatus) {
            if ($newStatus === 'confirmed' && !$reservation->confirmed_at) {
                $listDataUpdate['confirmed_at'] = now();
                // Tạo order mới khi cập nhật trạng thái sang confirmed
                $orderData = [
                    'order_type' => 'dine-in',
                    'reservation_id' => $reservation->id,
                    'customer_id' => $reservation->customer_id,
                    'notes' => $reservation->notes,
                    'contact_name' => $reservation->customer_name,
                    'contact_phone' => $reservation->customer_phone,
                    'contact_email' => $reservation->customer_email,
                    'user_id' => $reservation->user_id,
                    'tables' => is_array($data['table_id']) ? array_map(fn($id) => ['table_id' => $id], $data['table_id']) : ($data['table_id'] ? [['table_id' => $data['table_id']]] : []),
                    'status' => 'pending_confirmation',
                    'items' => [], // chưa có món
                ];
                $orderResult = $this->orderService->createOrder($orderData);
                $orderDataArr = $orderResult->getData();
                if (isset($orderDataArr['order'])) {
                    $order = $orderDataArr['order'];
                    event(new \App\Events\Orders\OrderCreated([
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'created_at' => $order->created_at,
                        'status' => $order->status,
                        'customer_name' => $order->contact_name,
                        // ... các trường khác nếu cần
                    ]));
                }
            } elseif ($newStatus === 'cancelled' && !$reservation->cancelled_at) {
                // Kiểm tra đơn hàng liên kết có rỗng không
                $orders = \App\Models\Order::where('reservation_id', $reservation->id)->get();
                foreach ($orders as $order) {
                    if ($order->items()->count() > 0) {
                        $result->setMessage(message: 'Không thể hủy đơn đặt bàn vì đã có món trong đơn hàng. Vui lòng kiểm tra lại!');
                        return $result;
                    }
                }
                $listDataUpdate['cancelled_at'] = now();
                // Hủy đơn hàng liên kết nếu có
                foreach ($orders as $order) {
                    $this->orderService->updateOrder(['status' => 'cancelled'], $order->id);
                }
            } elseif ($newStatus === 'completed' && !$reservation->completed_at) {
                // Kiểm tra đơn hàng liên kết có rỗng không
                $orders = \App\Models\Order::where('reservation_id', $reservation->id)->get();
                foreach ($orders as $order) {
                    if ($order->items()->count() == 0) {
                        $result->setMessage(message: 'Không thể hoàn thành đơn đặt bàn vì đơn hàng vẫn đang rỗng. Vui lòng kiểm tra lại!');
                        return $result;
                    }
                }
                $listDataUpdate['completed_at'] = now();
            }
        }

        $ok = $this->reservationRepository->updateByConditions(['id' => $reservation->id], $listDataUpdate);
        if ($ok && isset($data['table_id']) && is_array($data['table_id'])) {
            // Đồng bộ nhiều bàn với bảng reservation_tables
            $reservation->tables()->sync($data['table_id']);
        }
        if ($ok) {
            foreach ($listDataUpdate as $field => $newValue) {
                $oldValue = $reservation->$field ?? null;
                // So sánh, nếu khác thì log
                if ($field === 'reservation_time' || $field === 'reservation_date') {
                    // Có thể cần chuẩn hóa định dạng ngày/giờ trước khi so sánh
                    $oldValue = (string)$oldValue;
                    $newValue = (string)$newValue;
                }
                if ($oldValue != $newValue) {
                    $this->reservationRepository->createReservationChangeLog([
                        'reservation_id' => $reservation->id,
                        'user_id' => $data['user_id'],
                        'change_timestamp' => now(),
                        'change_type' => 'UPDATE',
                        'field_changed' => $field,
                        'old_value' => $oldValue,
                        'new_value' => $newValue,
                        'description' => 'Cập nhật đơn đặt bàn từ ' . $oldValue . ' sang ' . $newValue,
                    ]);
                }
            }
        }
        if (!$ok) {
            $result->setMessage(message: 'Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Cập nhật thành công!');

        // Dispatch event cho realtime khi thay đổi trạng thái
        if ($newStatus !== $currentStatus) {
            // Lấy danh sách admin từ bảng users, user_roles, roles
            $admins = \App\Models\User::select('users.id')
                ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('roles.role_name', 'Admin')
                ->get();
            foreach ($admins as $admin) {
                \App\Models\Notification::create([
                    'title' => 'Cập nhật trạng thái đơn đặt bàn',
                    'message' => 'Đơn đặt bàn của ' . $reservation->customer_name . ' chuyển từ ' . $currentStatus . ' sang ' . $newStatus,
                    'type' => 'reservation',
                    'reservation_id' => $reservation->id,
                    'order_id' => null,
                    'bill_id' => null,
                    'kitchen_order_id' => null,
                    'receiver_id' => $admin->id,
                    'read_at' => null,
                ]);
            }
            event(new ReservationStatusUpdated($reservation->toArray(), $currentStatus, $newStatus));
        }

        return $result;
    }
    public function listTrashedReservation(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination =  $this->reservationRepository->getTrashReservationList($filter, $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'customer_id' => $item->customer_id,
                'customer_name' => $item->customer_name,
                'customer_phone' => $item->customer_phone,
                'customer_email' => $item->customer_email,
                'reservation_time' => $item->reservation_time,
                'reservation_date' => $item->reservation_date,
                'number_of_guests' => $item->number_of_guests,
                'table_id' => $item->table_id,
                'notes' => $item->notes,
                'status' => $item->status,
                'user_id' => $item->user_id,
                'confirmed_at' => $item->confirmed_at,
                'cancelled_at' => $item->cancelled_at,
                'completed_at' => $item->completed_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }
    public function softDeleteReservation($id): DataAggregate
    {
        $result = new DataAggregate();
        $reservation = $this->reservationRepository->getByConditions(['id' => $id]);
        if (!$reservation) {
            $result->setMessage(message: 'Đơn đặt bàn không tồn tại');
            return $result;
        }
        // Lấy trạng thái
        $status = $reservation->status;
        // Lấy order liên kết
        $orders = \App\Models\Order::where('reservation_id', $reservation->id)->get();
        // Nếu completed thì không cho xóa
        if ($status === 'completed') {
            $result->setMessage(message: 'Đơn đặt bàn đã hoàn thành, không thể xóa để đảm bảo báo cáo/thống kê.');
            return $result;
        }
        // Nếu đã xác nhận (confirmed)
        if ($status === 'confirmed') {
            foreach ($orders as $order) {
                if ($order->items()->count() > 0) {
                    $result->setMessage(message: 'Đơn đặt bàn đã có món trong đơn hàng, không thể xóa.');
                    return $result;
                }
            }
        }
        // Nếu đã hủy thì chỉ xóa mềm (giữ record)
        // Các trạng thái khác (pending, cancelled, không có order) đều cho xóa
        $ok = $reservation->delete();
        if (!$ok) {
            $result->setMessage(message: 'Xóa thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa thành công!');
        return $result;
    }

    public function forceDeleteReservation($id): DataAggregate
    {
        $result = new DataAggregate();
        $reservation  = $this->reservationRepository->findOnlyTrashedById($id);

        $ok = $reservation->forceDelete();

        if (!$ok) {
            $result->setMessage(message: 'Xóa vĩnh viễn thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa vĩnh viễn thành công!');
        return $result;
    }

    public function restoreReservation($id): DataAggregate
    {
        $result = new DataAggregate();
        $reservation = $this->reservationRepository->findOnlyTrashedById($id);

        $ok = $reservation->restore();
        if (!$ok) {
            $result->setMessage(message: 'Khôi phục thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục thành công!');
        return $result;
    }

    public function confirmReservation(int $id, int $userId)
    {
        $result = new DataAggregate();

        $reservation = $this->reservationRepository->getByConditions(['id' => $id]);
        if (!$reservation) {
            $result->setResultError(message: 'Đơn đặt bàn không tồn tại');
            return $result;
        }

        if ($reservation->status !== 'pending') {
            $result->setResultError(message: 'Chỉ xác nhận đơn ở trạng thái chờ!');
            return $result;
        }

        // Tạo order mới khi xác nhận đơn đặt bàn
        $orderData = [
            'order_type' => 'dine-in',
            'reservation_id' => $reservation->id,
            'customer_id' => $reservation->customer_id,
            'notes' => $reservation->notes,
            'contact_name' => $reservation->customer_name,
            'contact_phone' => $reservation->customer_phone,
            'contact_email' => $reservation->customer_email,
            'user_id' => $userId,
            'tables' => $reservation->table_id ? [['table_id' => $reservation->table_id]] : [],
            'status' => 'pending_confirmation',
            'items' => [], // chưa có món
        ];
        $this->orderService->createOrder($orderData);

        // Chỉ update status, không cần truyền lại các trường khác
        $this->reservationRepository->updateByConditions(['id' => $id], [
            'status' => 'confirmed',
            'user_id' => $userId,
            'confirmed_at' => now(),
        ]);

        // Dispatch event cho realtime
        event(new ReservationStatusUpdated($reservation->toArray(), 'pending', 'confirmed'));

        $result->setResultSuccess(message: 'Xác nhận đơn đặt bàn thành công', data: ['new_status' => 'confirmed']);
        return $result;
    }

    public function countByStatus(): array
    {
        $listStatus = ['pending', 'confirmed', 'cancelled', 'completed', 'no_show', 'seated'];
        $counts = [];

        foreach ($listStatus as $status) {
            $counts[$status] = $this->reservationRepository->countByConditions(['status' => $status]);
        }
        return $counts;
    }

    /**
     * Lấy lịch sử thay đổi của đơn đặt bàn
     */
    public function getReservationChangeLogs(int $reservationId)
    {
        // Lấy danh sách log thay đổi
        $logs = $this->reservationRepository->getReservationChangeLogs($reservationId);
        // Lấy danh sách user_id duy nhất từ log
        $userIds = $logs->pluck('user_id')->filter()->unique()->toArray();
        $users = [];
        if (!empty($userIds)) {
            $users = \App\Models\User::whereIn('id', $userIds)
                ->get(['id', 'username', 'full_name'])
                ->keyBy('id')
                ->map(function ($user) {
                    return $user->username ?: $user->full_name;
                })->toArray();
        }
        // Gắn tên người thao tác vào từng log
        $logs = $logs->map(function ($log) use ($users) {
            $log->user_name = $users[$log->user_id] ?? null;
            return $log;
        });
        return $logs;
    }
}
