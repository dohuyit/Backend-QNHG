<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemChangeLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Dish;

class OrderRepository implements OrderRepositoryInterface
{
    public function getByConditions(array $conditions): ?Order
    {
        return Order::where($conditions)->first();
    }

    public function getListOrders(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Order::query()->with(['items.menuItem', 'table', 'reservation', 'customer', 'user']);

        if (isset($filter['order_type'])) {
            $query->where('order_type', $filter['order_type']);
        }

        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (isset($filter['payment_status'])) {
            $query->where('payment_status', $filter['payment_status']);
        }

        if (isset($filter['date_from'])) {
            $query->where('order_time', '>=', $filter['date_from']);
        }

        if (isset($filter['date_to'])) {
            $query->where('order_time', '<=', $filter['date_to']);
        }

        if (isset($filter['customer_id'])) {
            $query->where('customer_id', $filter['customer_id']);
        }

        if (isset($filter['user_id'])) {
            $query->where('user_id', $filter['user_id']);
        }

        if (isset($filter['table_id'])) {
            $query->where('table_id', $filter['table_id']);
        }

        if (isset($filter['reservation_id'])) {
            $query->where('reservation_id', $filter['reservation_id']);
        }

        if (isset($filter['query'])) {
            $query->where(function ($q) use ($filter) {
                $q->where('order_code', 'like', '%' . $filter['query'] . '%')
                    ->orWhere('contact_name', 'like', '%' . $filter['query'] . '%')
                    ->orWhere('contact_phone', 'like', '%' . $filter['query'] . '%');
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    public function getTrashOrderList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Order::onlyTrashed()->with(['items.menuItem', 'table', 'reservation', 'customer', 'user']);

        if (isset($filter['order_type'])) {
            $query->where('order_type', $filter['order_type']);
        }

        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (isset($filter['payment_status'])) {
            $query->where('payment_status', $filter['payment_status']);
        }

        if (isset($filter['date_from'])) {
            $query->where('order_time', '>=', $filter['date_from']);
        }

        if (isset($filter['date_to'])) {
            $query->where('order_time', '<=', $filter['date_to']);
        }

        if (isset($filter['customer_id'])) {
            $query->where('customer_id', $filter['customer_id']);
        }

        if (isset($filter['user_id'])) {
            $query->where('user_id', $filter['user_id']);
        }

        if (isset($filter['table_id'])) {
            $query->where('table_id', $filter['table_id']);
        }

        if (isset($filter['reservation_id'])) {
            $query->where('reservation_id', $filter['reservation_id']);
        }

        if (isset($filter['search'])) {
            $query->where(function ($q) use ($filter) {
                $q->where('order_code', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('contact_name', 'like', '%' . $filter['search'] . '%')
                    ->orWhere('contact_phone', 'like', '%' . $filter['search'] . '%');
            });
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($limit);
    }

    public function createOrder(array $data): Order
    {
        $total_amount = 0;
        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $menuItem = Dish::find($item['menu_item_id']);
                if ($menuItem) {
                    $total_amount += $menuItem->selling_price * ($item['quantity'] ?? 1);
                }
            }
        }

        $orderData = [
            'order_code' => $data['order_code'],
            'order_type' => $data['order_type'],
            'table_id' => $data['table_id'] ?? null,
            'reservation_id' => $data['reservation_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'user_id' => $data['user_id'],
            'order_time' => $data['order_time'],
            'status' => $data['status'],
            'payment_status' => $data['payment_status'],
            'notes' => $data['notes'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'total_amount' => $total_amount,
            'final_amount' => $total_amount,
        ];

        $order = Order::create($orderData);

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $item['order_id'] = $order->id;
                $item['kitchen_status'] = $item['kitchen_status'] ?? 'pending';
                OrderItem::create($item);
            }
        }

        return $order->load(['items.menuItem', 'table', 'reservation', 'customer', 'user', 'bill']);
    }

    public function updateOrder(string $id, array $data): Order
    {
        $order = Order::find($id);

        $total_amount = $order->total_amount;
        if (isset($data['items'])) {
            $total_amount = 0;
            foreach ($data['items'] as $item) {
                $menuItem = Dish::find($item['menu_item_id'] ?? ($order->items->find($item['id'])?->menu_item_id));
                if ($menuItem) {
                    $total_amount += $menuItem->selling_price * ($item['quantity'] ?? 1);
                }
            }
        }

        $orderData = [
            'order_type' => $data['order_type'],
            'table_id' => $data['table_id'] ?? $order->table_id,
            'reservation_id' => $data['reservation_id'] ?? $order->reservation_id,
            'customer_id' => $data['customer_id'] ?? $order->customer_id,
            'status' => $data['status'] ?? $order->status,
            'payment_status' => $data['payment_status'] ?? $order->payment_status,
            'notes' => $data['notes'] ?? $order->notes,
            'delivery_address' => $data['delivery_address'] ?? $order->delivery_address,
            'contact_name' => $data['contact_name'] ?? $order->contact_name,
            'contact_email' => $data['contact_email'] ?? $order->contact_email,
            'contact_phone' => $data['contact_phone'] ?? $order->contact_phone,
            'total_amount' => $total_amount,
            'final_amount' => $total_amount,
        ];

        $order->update($orderData);

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['id'])) {
                    OrderItem::where('id', $item['id'])->update($item);
                } else {
                    $item['order_id'] = $order->id;
                    $item['kitchen_status'] = $item['kitchen_status'] ?? 'pending';
                    OrderItem::create($item);
                }
            }
        }

        return $order->load(['items.menuItem', 'table', 'reservation', 'customer', 'user', 'bill']);
    }

    public function softDeleteOrder(string $id): void
    {
        $order = Order::find($id);
        $order->delete();
    }

    public function forceDeleteOrder(string $id): void
    {
        $order = Order::onlyTrashed()->find($id);
        $order->forceDelete();
    }

    public function restoreOrder(string $id): void
    {
        $order = Order::onlyTrashed()->find($id);
        $order->restore();
    }

    public function updateItemStatus(int $orderItemId, string $status, int $userId): OrderItem
    {
        $orderItem = OrderItem::find($orderItemId);
        $oldStatus = $orderItem->kitchen_status;
        $orderItem->update(['kitchen_status' => $status]);

        OrderItemChangeLog::create([
            'order_item_id' => $orderItemId,
            'order_id' => $orderItem->order_id,
            'user_id' => $userId,
            'change_type' => 'STATUS_UPDATE',
            'field_changed' => 'kitchen_status',
            'old_value' => $oldStatus,
            'new_value' => $status,
        ]);

        $order = Order::find($orderItem->order_id);
        $order->total_amount = $order->items->sum(function ($item) {
            return $item->quantity * ($item->menuItem->selling_price ?? 0);
        });
        $order->final_amount = $order->total_amount;
        $order->update(['total_amount' => $order->total_amount, 'final_amount' => $order->final_amount]);

        return $orderItem->load('menuItem');
    }

    public function countByConditions(array $conditions = []): int
    {
        $query = Order::query();

        if (isset($conditions['order_type'])) {
            $query->where('order_type', $conditions['order_type']);
        }

        if (isset($conditions['status'])) {
            $query->where('status', $conditions['status']);
        }

        if (isset($conditions['payment_status'])) {
            $query->where('payment_status', $conditions['payment_status']);
        }

        if (isset($conditions['date_from'])) {
            $query->where('order_time', '>=', $conditions['date_from']);
        }

        if (isset($conditions['date_to'])) {
            $query->where('order_time', '<=', $conditions['date_to']);
        }

        if (isset($conditions['customer_id'])) {
            $query->where('customer_id', $conditions['customer_id']);
        }

        if (isset($conditions['user_id'])) {
            $query->where('user_id', $conditions['user_id']);
        }

        if (isset($conditions['table_id'])) {
            $query->where('table_id', $conditions['table_id']);
        }

        if (isset($conditions['reservation_id'])) {
            $query->where('reservation_id', $conditions['reservation_id']);
        }

        return $query->count();
    }
}
