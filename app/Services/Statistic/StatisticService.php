<?php

namespace App\Services\Statistic;

use App\Common\DataAggregate;
use App\Repositories\Order\OrderRepositoryInterface;

class StatisticService
{
    protected OrderRepositoryInterface $orderRepository;
    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }
    public function getOrderRevenueStats(?string $startDate, ?string $endDate, string $groupBy): DataAggregate
    {
        $result = new DataAggregate();

        $revenueData = $this->orderRepository->getRevenueGroupedByTime($startDate, $endDate, $groupBy);

        if ($revenueData === null) {
            $result->setResultError('Lấy dữ liệu doanh thu thất bại');
            return $result;
        }

        $data = [];
        foreach ($revenueData as $item) {
            $data[] = [
                'time' => $item->time,
                'revenue' => (float)$item->total_revenue,
            ];
        }

        $result->setResultSuccess($data);
        return $result;
    }

}
