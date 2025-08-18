<?php

namespace App\Repositories\KitchenOrders;

use App\Models\KitchenOrder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface KitchenOrderRepositoryInterface
{
    public function getKitchenOrderList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?KitchenOrder;
    public function countByConditions(array $conditions): int;
    public function updateOrderItemStatus(int $orderItemId, string $newStatus): bool;
    public function create(array $data);

    public function areAllItemsReadyInOrder(int $orderId): bool;

    public function getAllKitchenOrdersByOrderItemId(int $orderItemId): ?Collection;
}
