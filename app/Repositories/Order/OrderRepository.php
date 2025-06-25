<?php

namespace App\Repositories\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderTable;
use App\Models\OrderItemChangeLog;
use App\Common\DataAggregate;
use App\Common\ListAggregate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = Order::where($conditions)->update($updateData);
        return (bool) $result;
    }

    public function getByConditions(array $conditions): ?Order
    {
        return Order::where($conditions)->first();
    }

    public function getListOrders(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Order::query()->with(['items', 'tables', 'customer']);

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

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    public function createOrder(array $data): DataAggregate
    {
        try {
            DB::beginTransaction();

            $order = Order::create($data);

            if (isset($data['items'])) {
                foreach ($data['items'] as $item) {
                    $item['order_id'] = $order->id;
                    OrderItem::create($item);
                }
            }

            if (isset($data['tables'])) {
                foreach ($data['tables'] as $table) {
                    $table['order_id'] = $order->id;
                    OrderTable::create($table);
                }
            }

            DB::commit();

            $result = new DataAggregate();
            $result->setResultSuccess(['order' => $order->load(['items', 'tables'])]);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = new DataAggregate();
            $result->setResultError('Không thể tạo đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function updateOrder(int $id, array $data): DataAggregate
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($id);
            $order->update($data);

            if (isset($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (isset($item['id'])) {
                        OrderItem::where('id', $item['id'])->update($item);
                    } else {
                        $item['order_id'] = $order->id;
                        OrderItem::create($item);
                    }
                }
            }

            DB::commit();

            $result = new DataAggregate();
            $result->setResultSuccess(['order' => $order->load(['items', 'tables'])]);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = new DataAggregate();
            $result->setResultError('Không thể cập nhật đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function getOrderDetail(int $id): DataAggregate
    {
        try {
            $order = Order::with(['items', 'tables', 'customer', 'user'])
                ->findOrFail($id);

            $result = new DataAggregate();
            $result->setResultSuccess(['order' => $order]);
            return $result;
        } catch (\Exception $e) {
            $result = new DataAggregate();
            $result->setResultError('Không tìm thấy đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function updateItemStatus(int $orderItemId, string $status, int $userId): DataAggregate
    {
        try {
            DB::beginTransaction();

            $orderItem = OrderItem::findOrFail($orderItemId);
            $oldStatus = $orderItem->kitchen_status;
            $orderItem->update(['kitchen_status' => $status]);

            // Log thay đổi
            OrderItemChangeLog::create([
                'order_item_id' => $orderItemId,
                'order_id' => $orderItem->order_id,
                'user_id' => $userId,
                'change_type' => 'STATUS_UPDATE',
                'field_changed' => 'kitchen_status',
                'old_value' => $oldStatus,
                'new_value' => $status
            ]);

            DB::commit();

            $result = new DataAggregate();
            $result->setResultSuccess(['order_item' => $orderItem]);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = new DataAggregate();
            $result->setResultError('Không thể cập nhật trạng thái món', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function splitOrder(int $orderId, array $items): DataAggregate
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($orderId);
            $newOrder = $order->replicate();
            $newOrder->save();

            foreach ($items as $itemId) {
                $orderItem = OrderItem::findOrFail($itemId);
                $orderItem->update(['order_id' => $newOrder->id]);
            }

            DB::commit();

            $result = new DataAggregate();
            $result->setResultSuccess([
                'original_order' => $order->load(['items', 'tables']),
                'new_order' => $newOrder->load(['items', 'tables'])
            ]);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = new DataAggregate();
            $result->setResultError('Không thể tách đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function mergeOrders(array $orderIds): DataAggregate
    {
        try {
            DB::beginTransaction();

            $mainOrder = Order::findOrFail($orderIds[0]);
            $ordersToMerge = Order::whereIn('id', array_slice($orderIds, 1))->get();

            foreach ($ordersToMerge as $order) {
                OrderItem::where('order_id', $order->id)
                    ->update(['order_id' => $mainOrder->id]);
                $order->delete();
            }

            DB::commit();

            $result = new DataAggregate();
            $result->setResultSuccess(['order' => $mainOrder->load(['items', 'tables'])]);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = new DataAggregate();
            $result->setResultError('Không thể gộp đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function getOrderItem(int $orderItemId): ?OrderItem
    {
        return OrderItem::with(['menuItem', 'statusHistory.user'])
            ->find($orderItemId);
    }

    public function getLastOrder(): ?Order
    {
        return Order::orderBy('order_code', 'desc')->first();
    }

    public function addOrderItem(string $orderId, array $data): DataAggregate
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($orderId);

            // Kiểm tra xem món đã tồn tại trong đơn hàng chưa
            $existingItem = OrderItem::where('order_id', $orderId)
                ->where('menu_item_id', $data['menu_item_id'])
                ->first();

            if ($existingItem) {
                // Nếu đã tồn tại, cập nhật số lượng
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $data['quantity']
                ]);
                $orderItem = $existingItem;
            } else {
                // Nếu chưa tồn tại, tạo mới
                $data['order_id'] = $orderId;
                $data['kitchen_status'] = 'pending';
                $orderItem = OrderItem::create($data);
            }

            // Log thay đổi
            OrderItemChangeLog::create([
                'order_item_id' => $orderItem->id,
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'change_type' => 'ADD_ITEM',
                'field_changed' => 'quantity',
                'old_value' => $existingItem ? $existingItem->quantity : 0,
                'new_value' => $orderItem->quantity
            ]);

            DB::commit();

            $result = new DataAggregate();
            $result->setResultSuccess(['order_item' => $orderItem->load('menuItem')]);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = new DataAggregate();
            $result->setResultError('Không thể thêm món vào đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function updateOrderItem(string $orderId, int $itemId, array $data): DataAggregate
    {
        try {
            DB::beginTransaction();

            $orderItem = OrderItem::where('order_id', $orderId)
                ->where('id', $itemId)
                ->firstOrFail();

            $oldData = $orderItem->toArray();
            $orderItem->update($data);

            // Log thay đổi cho từng trường
            foreach ($data as $field => $newValue) {
                if ($oldData[$field] != $newValue) {
                    OrderItemChangeLog::create([
                        'order_item_id' => $itemId,
                        'order_id' => $orderId,
                        'user_id' => Auth::id(),
                        'change_type' => 'UPDATE_ITEM',
                        'field_changed' => $field,
                        'old_value' => $oldData[$field],
                        'new_value' => $newValue
                    ]);
                }
            }

            DB::commit();

            $result = new DataAggregate();
            $result->setResultSuccess(['order_item' => $orderItem->load('menuItem')]);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = new DataAggregate();
            $result->setResultError('Không thể cập nhật món trong đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function deleteOrderItem(string $orderId, int $itemId): DataAggregate
    {
        try {
            DB::beginTransaction();

            $orderItem = OrderItem::where('order_id', $orderId)
                ->where('id', $itemId)
                ->firstOrFail();

            // Log thay đổi trước khi xóa
            OrderItemChangeLog::create([
                'order_item_id' => $itemId,
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'change_type' => 'DELETE_ITEM',
                'field_changed' => 'all',
                'old_value' => json_encode($orderItem->toArray()),
                'new_value' => null
            ]);

            $orderItem->delete();

            DB::commit();

            $result = new DataAggregate();
            $result->setResultSuccess(['message' => 'Xóa món thành công']);
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            $result = new DataAggregate();
            $result->setResultError('Không thể xóa món khỏi đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
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

        return $query->count();
    }
}
