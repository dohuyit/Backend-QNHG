<?php

namespace App\Http\Controllers\Admin\Statistics;

use App\Http\Controllers\Controller;
use App\Services\Statistic\StatisticService;
use App\Services\Reservations\ReservationService;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    protected StatisticService $statisticService;
    protected ReservationService $reservationService;
    public function __construct(ReservationService $reservationService, StatisticService $statisticService)
    {
        $this->reservationService = $reservationService;
        $this->statisticService = $statisticService;
    }

    public function getReservationStatusStats()
    {
        $result = $this->reservationService->getReservationStatusStats();

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(data: $result->getData());
    }

    public function getReservationTimeStats(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $groupBy = $request->query('group_by', 'day');

        $result = $this->reservationService->getReservationTimeStats($startDate, $endDate, $groupBy);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(data: $result->getData());
    }

    public function getOrderRevenueStats(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $groupBy = $request->query('group_by', 'day'); // day, month, quarter, year

        $result = $this->statisticService->getOrderRevenueStats($startDate, $endDate, $groupBy);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(data: $result->getData());
    }

}
