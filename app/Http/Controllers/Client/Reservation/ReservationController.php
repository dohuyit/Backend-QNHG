<?php

namespace App\Http\Controllers\Client\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest\StoreReservationRequest;
use App\Repositories\Reservations\ReservationRepositoryInterface;
use App\Services\Reservations\ReservationClientService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected ReservationClientService $reservationService;
    protected ReservationRepositoryInterface $reservationRepository;
    public function __construct(
        ReservationClientService $reservationService,
        ReservationRepositoryInterface $reservationRepository,
    ) {
        $this->reservationService = $reservationService;
        $this->reservationRepository = $reservationRepository;
    }
    public function bookTableByClient(StoreReservationRequest $request)
    {
        $data = $request->only([
            'customer_id',
            'customer_name',
            'customer_phone',
            'customer_email',
            'reservation_date',
            'reservation_time',
            'number_of_guests',
            'notes',
            'status'
        ]);

        $result = $this->reservationService->createClientReservation($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
}
