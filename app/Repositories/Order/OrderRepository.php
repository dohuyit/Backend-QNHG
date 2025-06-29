<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemChangeLog;
use App\Models\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Dish;
use App\Models\KitchenOrder;
use App\Models\OrderTable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderRepository implements OrderRepositoryInterface
{
    public function getByConditions(array $conditions): ?Order
    {
        return Order::where($conditions)->first();
    }

    public function getListOrders(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Order::query()->with(['items.menuItem', 'tables', 'reservation', 'customer', 'user']);

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

    public function createOrder(array $orderData, array $items, array $tables): ?Order
    {
        try {
            DB::beginTransaction();

            // Tạo đơn hàng
            $order = Order::create($orderData);

            // Tạo OrderItem và KitchenOrder nếu có món
            foreach ($items as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'dish_id' => $item['dish_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'kitchen_status' => $item['kitchen_status'],
                    'notes' => $item['notes'],
                    'is_priority' => $item['is_priority'],
                ]);

                KitchenOrder::create([
                    'order_item_id' => $orderItem->id,
                    'order_id' => $order->id,
                    'table_number' => $order->tables()->first()->table->number ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'],
                    'status' => 'pending',
                    'is_priority' => $item['is_priority'],
                    'received_at' => now(),
                ]);
            }

            // Tạo OrderTable nếu có bàn
            foreach ($tables as $table) {
                OrderTable::create([
                    'order_id' => $order->id,
                    'table_id' => $table['table_id'],
                ]);
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[OrderRepository] Failed to create order: ' . $e->getMessage());
            return null;
        }
    }

    public function updateOrder(Order $order, array $orderData, array $items, array $tables): ?Order
    {
        try {
            DB::beginTransaction();

            // Lấy danh sách dish_id mới
            $menuItemIds = collect($items)->pluck('dish_id')->unique()->toArray();
            $menuItems = Dish::whereIn('id', $menuItemIds)->get()->keyBy('id');

            $totalAmount = 0;

            // Xóa toàn bộ OrderItem cũ
            OrderItem::where('order_id', $order->id)->delete();

            // Xóa toàn bộ KitchenOrder cũ liên quan đến order
            KitchenOrder::where('order_id', $order->id)->delete();

            // Tính lại tổng tiền + tạo OrderItem & KitchenOrder mới
            foreach ($items as $item) {
                $menuItem = $menuItems->get($item['dish_id']);
                if (!$menuItem) {
                    throw new \Exception("Món ăn ID {$item['dish_id']} không tồn tại");
                }

                $lineTotal = $menuItem->selling_price * $item['quantity'];
                $totalAmount += $lineTotal;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'dish_id' => $item['dish_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $menuItem->selling_price,
                    'kitchen_status' => $item['kitchen_status'] ?? 'pending',
                ]);

                // Tạo phiếu bếp cho mỗi OrderItem mới
                KitchenOrder::create([
                    'order_item_id' => $orderItem->id,
                    'order_id' => $order->id,
                    'table_number' => $order->tables()->first()->table->number ?? null,
                    'item_name' => $menuItem->name,
                    'quantity' => $item['quantity'],
                    'notes' => $item['notes'] ?? null,
                    'status' => 'pending',
                    'is_priority' => $item['is_priority'] ?? false,
                    'received_at' => now(),
                ]);
            }

            // Cập nhật tổng tiền
            $orderData['total_amount'] = $totalAmount;
            $orderData['final_amount'] = $totalAmount;

            // Cập nhật thông tin order
            $order->update($orderData);

            // Xóa OrderTable cũ
            OrderTable::where('order_id', $order->id)->delete();

            // Tạo lại OrderTable nếu order_type là dine-in
            if (($orderData['order_type'] ?? $order->order_type) === 'dine-in' && !empty($tables)) {
                foreach ($tables as $table) {
                    $tableId = is_array($table) ? ($table['table_id'] ?? $table['id'] ?? $table) : $table;
                    $tableModel = Table::find($tableId);
                    if (!$tableModel) {
                        throw new \Exception("Bàn ID {$tableId} không tồn tại");
                    }
                    OrderTable::create([
                        'order_id' => $order->id,
                        'table_id' => $tableModel->id,
                    ]);
                }
            }

            DB::commit();
            return $order->refresh()->load(['items.menuItem', 'tables.tableItem']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("[OrderRepository] Failed to update order: " . $e->getMessage());
            return null;
        }
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
