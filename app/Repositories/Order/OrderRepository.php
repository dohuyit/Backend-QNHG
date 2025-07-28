<?php

namespace App\Repositories\Order;

use App\Models\Combo;
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

        if (isset($filter['order_code'])) {
            $query->where('order_code', $filter['order_code']);
        }

        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
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
        $query = Order::onlyTrashed()->with(['items.menuItem', 'tables', 'reservation', 'customer', 'user']);

        if (isset($filter['order_type'])) {
            $query->where('order_type', $filter['order_type']);
        }

        if (isset($filter['status'])) {
            $query->where('status', $filter['status']);
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
                    'dish_id' => $item['dish_id'] ?? null,
                    'combo_id' => $item['combo_id'] ?? null,
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
            $tableIds = [];
            foreach ($tables as $table) {
                $tableId = $table['table_id'] ?? $table['id'] ?? $table;
                OrderTable::create([
                    'order_id' => $order->id,
                    'table_id' => $tableId,
                ]);
                $tableIds[] = $tableId;
            }

            // ✅ Đánh dấu trạng thái bàn sang occupied
            if (!empty($tableIds)) {
                Table::whereIn('id', $tableIds)->update(['status' => 'occupied']);
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
            // Fetch all existing OrderItems for the order, keyed by ID
            $existingItems = OrderItem::where('order_id', $order->id)->get()->keyBy('id');

            // Prepare menu items for validation and pricing
            $dishIds = collect($items)->pluck('dish_id')->filter()->unique()->toArray();
            $comboIds = collect($items)->pluck('combo_id')->filter()->unique()->toArray();
            $menuItems = Dish::whereIn('id', $dishIds)->get()->keyBy('id');
            $comboItems = Combo::whereIn('id', $comboIds)->get()->keyBy('id');

            $totalAmount = 0;

            // Process each item in the incoming data
            foreach ($items as $item) {
                $quantity = (int)($item['quantity'] ?? 1);
                $itemName = null;
                $unitPrice = null;

                // Validate dish_id or combo_id
                if (!empty($item['dish_id'])) {
                    $menuItem = $menuItems->get($item['dish_id']);
                    $unitPrice = $menuItem->selling_price;
                    $itemName = $menuItem->name;
                } elseif (!empty($item['combo_id'])) {
                    $comboItem = $comboItems->get($item['combo_id']);
                    $unitPrice = $comboItem->selling_price;
                    $itemName = $comboItem->name;
                }

                // Trường hợp quantity <= 0: xóa OrderItem nếu tồn tại
                if ($quantity <= 0 && !empty($item['id']) && $existingItems->has($item['id'])) {
                    OrderItem::where('id', $item['id'])->delete();
                    KitchenOrder::where('order_item_id', $item['id'])->delete();
                    continue; // sang item tiếp theo
                }

                $lineTotal = $unitPrice * max(1, $quantity);
                $totalAmount += $lineTotal;

                // Nếu đã có id và tồn tại -> update
                if (!empty($item['id']) && $existingItems->has($item['id'])) {
                    $orderItem = $existingItems->get($item['id']);
                    $orderItem->update([
                        'dish_id' => $item['dish_id'] ?? null,
                        'combo_id' => $item['combo_id'] ?? null,
                        'quantity' => max(1, $quantity),
                        'unit_price' => $unitPrice,
                    ]);

                    // Update corresponding KitchenOrder
                    KitchenOrder::where('order_item_id', $orderItem->id)->update([
                        'item_name' => $itemName,
                        'quantity' => max(1, $quantity),
                        'notes' => $item['notes'] ?? null,
                        'is_priority' => $item['is_priority'] ?? false,
                    ]);
                } else {
                    // Tạo mới OrderItem
                    $newItem = OrderItem::create([
                        'order_id' => $order->id,
                        'dish_id' => $item['dish_id'] ?? null,
                        'combo_id' => $item['combo_id'] ?? null,
                        'quantity' => max(1, $quantity),
                        'unit_price' => $unitPrice,
                        'kitchen_status' => $item['kitchen_status'] ?? 'pending',
                    ]);

                    // Tạo KitchenOrder tương ứng
                    KitchenOrder::create([
                        'order_item_id' => $newItem->id,
                        'order_id' => $order->id,
                        'table_number' => $order->tables()->first()->table->number ?? null,
                        'item_name' => $itemName,
                        'quantity' => max(1, $quantity),
                        'notes' => $item['notes'] ?? null,
                        'status' => 'pending',
                        'is_priority' => $item['is_priority'] ?? false,
                        'received_at' => now(),
                    ]);
                }
            }

            // Không xóa OrderItem nào khác không được gửi lên!

            // Update total amount
            $orderData['total_amount'] = $totalAmount;
            $orderData['final_amount'] = $totalAmount;

            // Update order details
            $order->update($orderData);

            if (($orderData['order_type'] ?? $order->order_type) === 'dine-in' && !empty($tables)) {
                // 1. Phân tách bàn cũ, bàn mới có status=available, bàn mới còn lại
                $oldTableIds = [];
                $newAvailableTables = [];
                $newOccupiedTables = [];

                foreach ($tables as $table) {
                    if (isset($table['old_id_table'])) {
                        $oldTableIds[] = (int)$table['old_id_table'];
                    }
                    if (isset($table['new_id_table'])) {
                        $tableId = (int)$table['new_id_table'];
                        if (isset($table['status']) && $table['status'] === 'available') {
                            $newAvailableTables[] = $tableId; // bàn mới đang available → occupied
                        } else {
                            $newOccupiedTables[] = $tableId;  // bàn mới bình thường
                        }
                    }
                }

                // 2. Cập nhật trạng thái bàn cũ về available
                if (!empty($oldTableIds)) {
                    Table::whereIn('id', $oldTableIds)->update(['status' => 'available']);
                }

                // 3. Cập nhật trạng thái bàn mới (status=available) sang occupied
                if (!empty($newAvailableTables)) {
                    Table::whereIn('id', $newAvailableTables)->update(['status' => 'occupied']);
                }

                // 4. Cập nhật trạng thái bàn mới khác sang occupied (nếu cần)
                if (!empty($newOccupiedTables)) {
                    Table::whereIn('id', $newOccupiedTables)->update(['status' => 'occupied']);
                }

                // Gom tất cả bàn mới lại để tạo OrderTable mới
                $allNewTables = array_unique(array_merge($newAvailableTables, $newOccupiedTables));

                // 5. Xóa OrderTable cũ
                OrderTable::where('order_id', $order->id)->delete();

                // 6. Tạo OrderTable mới cho các bàn đã chọn
                foreach ($allNewTables as $tableId) {
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

            // 6. Nếu đơn hàng chuyển sang trạng thái hoàn thành, đưa bàn về cleaning
            if (($orderData['status'] ?? $order->status) === 'completed') {
                $currentTableIds = OrderTable::where('order_id', $order->id)->pluck('table_id')->toArray();
                if (!empty($currentTableIds)) {
                    Table::whereIn('id', $currentTableIds)->update(['status' => 'cleaning']);
                }
            }

            DB::commit();
            return $order->refresh()->load(['items.menuItem', 'tables']);
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

    public function updateByConditions(array $conditions, array $data): bool
    {
        return Order::where($conditions)->update($data);
    }

    public function getOrderByTableId($tableId): ?Order
    {
        return Order::where('table_id', $tableId)
            ->whereIn('status', ['pending', 'processing', 'confirmed'])
            ->orderByDesc('created_at')
            ->first();
    }
}
