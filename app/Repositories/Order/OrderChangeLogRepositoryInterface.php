<?php

namespace App\Repositories\Order;

use Illuminate\Support\Collection;

interface OrderChangeLogRepositoryInterface
{
    /**
     * Lấy lịch sử thay đổi của 1 đơn hàng
     */
    public function getOrderChangeLogs(int $orderId): Collection;

    /**
     * Tạo log thay đổi đơn hàng
     */
    public function createOrderChangeLog(array $data);

    /**
     * Lấy toàn bộ lịch sử thay đổi đơn hàng
     */
    public function getAllOrderChangeLogs(): Collection;
}
