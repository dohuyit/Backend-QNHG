<?php

namespace App\Repositories\Order;

use App\Models\OrderChangeLog;
use Illuminate\Support\Collection;

class OrderChangeLogRepository implements OrderChangeLogRepositoryInterface
{
    public function getOrderChangeLogs(int $orderId): Collection
    {
        return OrderChangeLog::where('order_id', $orderId)
            ->orderByDesc('change_timestamp')
            ->get();
    }

    public function createOrderChangeLog(array $data)
    {
        return OrderChangeLog::create($data);
    }

    public function getAllOrderChangeLogs(): Collection
    {
        return OrderChangeLog::query()
            ->leftJoin('orders', 'order_change_logs.order_id', '=', 'orders.id')
            ->orderByDesc('order_change_logs.change_timestamp')
            ->get(['order_change_logs.*', 'orders.order_code']);
    }
}
