<?php

namespace App\Http\Controllers\Admin\Reservation;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReservationRequest\StoreReservationRequest;
use App\Http\Requests\ReservationRequest\UpdateReservationRequest;
use App\Repositories\Reservations\ReservationRepositoryInterface;
use App\Services\Reservations\ReservationService;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    protected ReservationService $reservationService;
    protected ReservationRepositoryInterface $reservationRepository;
    public function __construct(
        ReservationService $reservationService,
        ReservationRepositoryInterface $reservationRepository,
    ) {
        $this->reservationService = $reservationService;
        $this->reservationRepository = $reservationRepository;
    }

    public function getListReservations()
    {
        $params = request()->only(
            'page',
            'limit',
            'query',
            'customer_id',
            'customer_name',
            'customer_phone',
            'customer_email',
            'reservation_time',
            'reservation_date',
            'number_of_guests',
            'table_id',
            'notes',
            'status',
            'user_id',
            'confirmed_at',
            'cancelled_at',
            'completed_at',
        );
        $result = $this->reservationService->getListReservation($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function createReservation(StoreReservationRequest $request)
    {
        $data = $request->only([
            'customer_id',
            'customer_name',
            'customer_phone',
            'customer_email',
            'reservation_date',
            'reservation_time',
            'number_of_guests',
            'table_id',
            'notes',
            'status',
            'user_id',
            'confirmed_at',
            'cancelled_at',
            'completed_at',
        ]);

        $result = $this->reservationService->createReservation($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function getReservationDetail(int $id)
    {
        $result = $this->reservationService->getReservationDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
    public function updateReservation(UpdateReservationRequest $request, int $id)
    {
        $data = $request->only([
            'customer_id',
            'customer_name',
            'customer_phone',
            'customer_email',
            'reservation_date',
            'reservation_time',
            'number_of_guests',
            'table_id',
            'notes',
            'status',
            'user_id',
            'confirmed_at',
            'cancelled_at',
            'completed_at',
        ]);

        $reservation  = $this->reservationRepository->getByConditions(['id' => $id]);
        if (!$reservation) {
            return $this->responseFail(message: 'Đơn đặt bàn không tồn tại', statusCode: 404);
        }

        $result = $this->reservationService->updateReservation($data, $reservation);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function listTrashedReservation(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'query',
            'customer_id',
            'customer_name',
            'customer_phone',
            'customer_email',
            'reservation_date',
            'reservation_time',
            'number_of_guests',
            'table_id',
            'notes',
            'status',
            'user_id',
            'confirmed_at',
            'cancelled_at',
            'completed_at',
        );
        $result = $this->reservationService->listTrashedReservation($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function softDeleteReservation(int $id)
    {
        $reservation = $this->reservationRepository->getByConditions(['id' => $id]);
        if (!$reservation) {
            return $this->responseFail(message: 'Đơn đặt bàn không tồn tại', statusCode: 404);
        }
        $result = $this->reservationService->softDeleteReservation($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function restoreReservation(int $id)
    {
        $result = $this->reservationService->restoreReservation($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function forceDeleteReservation($id)
    {
        $result = $this->reservationService->forceDeleteReservation($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function confirmReservation(Request $request, int $id)
    {
        $userId = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->id : 1; // nếu không có user thì mặc định bằng 1
        $result = $this->reservationService->confirmReservation($id, $userId);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: 'Xác nhận đơn đặt bàn thành công');
    }


    public function countByStatus()
    {
        $result = $this->reservationService->countByStatus();

        return $this->responseSuccess($result);
    }
}
