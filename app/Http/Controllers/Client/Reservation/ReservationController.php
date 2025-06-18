<?php

namespace App\Http\Controllers\Client\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest\StoreReservationRequest;
use App\Http\Requests\ReservationRequest\UpdateReservationRequest;
use App\Repositories\Reservations\ReservationRepositoryInterface;
use App\Services\Reservations\ReservationClientService;
use App\Services\Reservations\ReservationService;
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
            'reservation_time',
            'number_of_guests',
            'table_id',
            'notes',
            'status'
        ]);
        
        // if (auth()->check()) {
        //     $data['user_id'] = auth()->id();
        // }

        $result = $this->reservationService->createClientReservation($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
   
}
