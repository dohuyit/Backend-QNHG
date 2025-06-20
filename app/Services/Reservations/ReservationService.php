<?php

namespace App\Services\Reservations;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\Reservation;
use App\Repositories\Reservations\ReservationRepositoryInterface;

class ReservationService
{
    protected ReservationRepositoryInterface $reservationRepository;
    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }
    public function getListReservation(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;

        $pagination = $this->reservationRepository->getReservationList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'customer_id' => $item->customer_id,
                'customer_name' => $item->customer_name,
                'customer_phone' => $item->customer_phone,
                'customer_email' => $item->customer_email,
                'reservation_time' => $item->reservation_time,
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
    public function createReservation(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $listDataCreate = [
            'customer_id' => $data['customer_id'],
            'customer_name' => $data['customer_name'],
            'customer_phone' => $data['customer_phone'],
            'customer_email' => $data['customer_email'],
            'reservation_time' => $data['reservation_time'],
            'number_of_guests' => $data['number_of_guests'],
            'table_id' => $data['table_id'],
            'notes' => $data['notes'],
            'status' => $data['status'] ?? 'pending',
            'user_id' => $data['user_id'],
        ];

        $ok  = $this->reservationRepository->createData($listDataCreate);
        if (!$ok) {
            $result->setMessage(message: 'Thêm mới thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Thêm mới thành công!');
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

        $result->setResultSuccess(data: ['reservation' => $reservation]);
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
            'number_of_guests' => $data['number_of_guests'],
            'table_id' => $data['table_id'],
            'notes' => $data['notes'],
            'status' => $data['status'] ?? $reservation->status,
            'user_id' => $data['user_id'],
        ];

        $newStatus = $listDataUpdate['status'];
        $currentStatus = $reservation->status;

        if ($newStatus !== $currentStatus) {
            if ($newStatus === 'confirmed' && !$reservation->confirmed_at) {
                $listDataUpdate['confirmed_at'] = now();
            } elseif ($newStatus === 'cancelled' && !$reservation->cancelled_at) {
                $listDataUpdate['cancelled_at'] = now();
            } elseif ($newStatus === 'completed' && !$reservation->completed_at) {
                $listDataUpdate['completed_at'] = now();
            }
        }

        $ok = $this->reservationRepository->updateByConditions(['id' => $reservation->id], $listDataUpdate);
        if (!$ok) {
            $result->setMessage(message: 'Cập nhật thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Cập nhật thành công!');
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
        $category = $this->reservationRepository->getByConditions(['id' => $id]);
        $ok = $category->delete();
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
        if (empty($userId)) {
            $result = new DataAggregate();
            $result->setResultError(message: 'Thiếu thông tin người xác nhận!');
            return $result;
        }
        $reservation = $this->reservationRepository->getByConditions(['id' => $id]);
        if (!$reservation) {
            $result = new DataAggregate();
            $result->setResultError(message: 'Đơn đặt bàn không tồn tại');
            return $result;
        }

        $data = [
            'status' => 'confirmed',
            'confirmed_at' => now(),
            'user_id' => $userId,
        ];

        $ok = $this->reservationRepository->confirmReservation($id, $userId);
        if (!$ok) {
            $result = new DataAggregate();
            $result->setResultError(message: 'Xác nhận thất bại, vui lòng thử lại!');
            return $result;
        }

        return $this->updateReservation($data, $reservation);
    }
}
