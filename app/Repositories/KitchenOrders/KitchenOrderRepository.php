<?php

namespace App\Repositories\KitchenOrders;

use App\Models\KitchenOrder;
use App\Models\OrderItem;
use App\Repositories\KitchenOrders\KitchenOrderRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class KitchenOrderRepository implements KitchenOrderRepositoryInterface
{

    private function filterKitchenOrders(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['status'] ?? null) {
            $query->where('status', $val);
        }

        if ($val = $filter['is_priority'] ?? null) {
            $query->where('is_priority', $val);
        }

        if ($val = $filter['item_name'] ?? null) {
            $query->whereHas('item_name', function ($q) use ($val) {
                $q->where('name', 'like', "%$val%");
            });
        }

        return $query;
    }
    public function getKitchenOrderList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = KitchenOrder::query();

        if (!empty($filter)) {
            $result = $this->filterKitchenOrders($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }
    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = KitchenOrder::where($conditions)->update($updateData);
        return (bool)$result;
    }
    public function getByConditions(array $conditions): ?KitchenOrder
    {
        $result = KitchenOrder::where($conditions)->first();
        return $result;
    }
    public function countByConditions(array $conditions = []): int
    {
        $query = KitchenOrder::query();

        if (!empty($conditions)) {
            $this->filterKitchenOrders($query, $conditions);
        }
        return $query->count();
    }

    public function updateOrderItemStatus(int $orderItemId, string $newStatus): bool
    {
        return OrderItem::where('id', $orderItemId)->update([
            'kitchen_status' => $newStatus,
        ]) > 0;
    }

    public function create(array $data)
    {
        return \App\Models\KitchenOrder::create($data);
    }
}
