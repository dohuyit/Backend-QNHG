<?php
namespace App\Repositories\KitchenOrders;

use App\Models\KitchenOrder;
use App\Models\OrderItem;
use App\Repositories\KitchenOrders\KitchenOrderRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class KitchenOrderRepository implements KitchenOrderRepositoryInterface
{

    public function filterKitchenOrders(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['status'] ?? null) {
            $query->where('status', $val);
        }

        if ($val = $filter['is_priority'] ?? null) {
            $query->where('is_priority', $val);
        }

        if ($val = $filter['item_name'] ?? null) {
            $query->where('item_name', 'like', "%$val%");
        }

        // 🔎 Thêm search tổng hợp
        if ($val = $filter['search'] ?? null) {
            $query->where(function ($q) use ($val) {
                $q->where('kitchen_orders.id', 'like', "%$val%") // mã đơn bếp
                    ->orWhere('item_name', 'like', "%$val%")
                    ->orWhere('combo_name', 'like', "%$val%");

                // join với orders
                $q->orWhereHas('order', function ($q2) use ($val) {
                    $q2->where('order_code', 'like', "%$val%");
                });

                // join với tables
                $q->orWhereHas('order.tables', function ($q3) use ($val) {
                    $q3->where('table_number', 'like', "%$val%");
                });
            });
        }

        return $query;
    }

    public function getKitchenOrderList(array $filter = [], int $limit = 1000): LengthAwarePaginator {
        $query = KitchenOrder::with(['order', 'order.tables']); // nhớ eager load

        if (! empty($filter)) {
            $query = $this->filterKitchenOrders($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = KitchenOrder::where($conditions)->update($updateData);
        return (bool) $result;
    }
    public function getByConditions(array $conditions): ?KitchenOrder
    {
        $result = KitchenOrder::where($conditions)->first();
        return $result;
    }
    public function countByConditions(array $conditions = []): int
    {
        $query = KitchenOrder::query();

        if (! empty($conditions)) {
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

    public function areAllItemsReadyInOrder(int $orderId): bool
    {
        return OrderItem::where('order_id', $orderId)
            ->where('kitchen_status', '!=', 'ready')
            ->doesntExist();
    }

    public function getAllKitchenOrdersByOrderItemId(int $orderItemId): ?Collection
    {
        try {
            $kitchenOrders = KitchenOrder::where('order_item_id', $orderItemId)
                ->get();

            if ($kitchenOrders->isEmpty()) {
                return null;
            }

            return $kitchenOrders;
        } catch (\Exception $e) {
            return null;
        }
    }
}
