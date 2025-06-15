<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Common\DataAggregate;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\OrderItem;

interface OrderRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;

    public function getByConditions(array $conditions): ?Order;

    public function getListOrders(array $filter = [], int $limit = 10): LengthAwarePaginator;

    public function createOrder(array $data): DataAggregate;

    public function updateOrder(int $id, array $data): DataAggregate;

    public function getOrderDetail(int $id): DataAggregate;

    public function updateItemStatus(int $orderItemId, string $status, int $userId): DataAggregate;

    public function splitOrder(int $orderId, array $items): DataAggregate;

    public function mergeOrders(array $orderIds): DataAggregate;

    public function getOrderItem(int $orderItemId): ?OrderItem;

    public function getLastOrder(): ?Order;

    public function addOrderItem(string $orderId, array $data): DataAggregate;

    public function updateOrderItem(string $orderId, int $itemId, array $data): DataAggregate;

    public function deleteOrderItem(string $orderId, int $itemId): DataAggregate;
}
