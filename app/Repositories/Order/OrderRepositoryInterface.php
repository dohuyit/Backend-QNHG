<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Common\DataAggregate;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\OrderItem;

interface OrderRepositoryInterface
{
    public function getByConditions(array $conditions): ?Order;

    public function getListOrders(array $filter = [], int $limit = 10): LengthAwarePaginator;

    public function getTrashOrderList(array $filter = [], int $limit = 10): LengthAwarePaginator;

    public function createOrder(array $orderData, array $items, array $tables): ?Order;

    public function updateOrder(Order $order, array $orderData, array $items, array $tables): ?Order;

    public function updateItemStatus(int $orderItemId, string $status, int $userId): OrderItem;

    public function softDeleteOrder(string $id): void;

    public function forceDeleteOrder(string $id): void;

    public function restoreOrder(string $id): void;

    public function countByConditions(array $conditions): int;
}
