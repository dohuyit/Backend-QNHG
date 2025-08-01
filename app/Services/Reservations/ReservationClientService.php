<?php

namespace App\Services\Reservations;

use App\Common\DataAggregate;
use App\Events\Reservations\ReservationCreated;
use App\Repositories\Reservations\ReservationRepositoryInterface;
use App\Repositories\Table\TableRepositoryInterface;
use App\Services\Mails\ReservationMailService;

class ReservationClientService
{
    protected ReservationRepositoryInterface $reservationRepository;
    protected TableRepositoryInterface $tableRepository;
    protected ReservationMailService  $reservationMailService;

    public function __construct(ReservationRepositoryInterface $reservationRepository, TableRepositoryInterface $tableRepository, ReservationMailService $reservationMailService)
    {
        $this->reservationRepository = $reservationRepository;
        $this->tableRepository = $tableRepository;
        $this->reservationMailService = $reservationMailService;
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
            'reservation_date' => $data['reservation_date'],
            'number_of_guests' => $data['number_of_guests'],
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

       


        // Dispatch event cho realtime
        event(new ReservationCreated($listDataCreate));

        $this->reservationMailService->sendClientConfirmMail($listDataCreate);
        return $result;
    }
}
