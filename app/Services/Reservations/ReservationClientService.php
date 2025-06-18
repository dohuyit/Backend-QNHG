<?php

namespace App\Services\Reservations;

use App\Common\DataAggregate;
use App\Repositories\Reservations\ReservationRepositoryInterface;

class ReservationClientService
{
    protected ReservationRepositoryInterface $reservationRepository;
    public function __construct(ReservationRepositoryInterface $reservationRepository)
    {
        $this->reservationRepository = $reservationRepository;
    }
    public function createClientReservation(array $data): DataAggregate
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
            'user_id' => $data['user_id'] ?? null,
            'status' => 'pending',
        ];

        $ok  = $this->reservationRepository->createData($listDataCreate);
        if (!$ok) {
            $result->setMessage(message: 'Đặt bàn thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Đặt bàn thành công!');
        return $result;
    }

}
