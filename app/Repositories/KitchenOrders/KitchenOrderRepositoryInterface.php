<?php
namespace App\Repositories\KitchenOrders;

use App\Models\KitchenOrder;
use Illuminate\Pagination\LengthAwarePaginator;

interface KitchenOrderRepositoryInterface
{
    public function getKitchenOrderList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?KitchenOrder;
    public function countByConditions(array $conditions): int;

}