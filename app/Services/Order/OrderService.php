<?php

namespace App\Services\Order;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\Combo;
use App\Models\Dish;
use App\Models\Order;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class OrderService
{
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getListOrders(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10000;
        $pagination = $this->orderRepository->getListOrders(filter: $filter, limit: $limit);
        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'order_code' => $item->order_code,
                'order_type' => $item->order_type,
                'tables' => $item->tables->map(function ($table) {
                    return [
                        'id' => (string)$table->id,
                        'table_number' => $table->table_number,
                    ];
                })->toArray(),
                'reservation' => $item->reservation ? [
                    'id' => (string)$item->reservation->id,
                    'reservation_time' => $item->reservation->reservation_time,
                ] : null,
                'customer' => $item->customer ? [
                    'id' => (string)$item->customer->id,
                    'full_name' => $item->customer->full_name,
                    'phone_number' => $item->customer->phone_number,
                ] : null,
                'user' => $item->user ? [
                    'id' => (string)$item->user->id,
                    'name' => $item->user->name,
                ] : null,
                'items' => $item->items->map(function ($item) {
                    return [
                        'id' => (string)$item->id,
                        'dish_id' => $item->menuItem ? [
                            'id' => (string)$item->menuItem->id,
                            'name' => $item->menuItem->name,
                        ] : null,
                        'combo_id' => $item->combo ? [
                            'id' => (string)$item->combo->id,
                            'name' => $item->combo->name,
                        ] : null,
                        'unit_price' => $item->unit_price,
                        'quantity' => $item->quantity,
                        'notes' => $item->notes,
                        'kitchen_status' => $item->kitchen_status,
                    ];
                })->toArray(),
                'status' => $item->status,
                'notes' => $item->notes,
                'delivery_address' => $item->delivery_address,
                'contact_name' => $item->contact_name,
                'contact_email' => $item->contact_email,
                'contact_phone' => $item->contact_phone,
                'total_amount' => $item->total_amount,
                'final_amount' => $item->final_amount,
                'order_time' => $item->order_time,
                'delivered_at' => $item->delivered_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }

    public function createOrder(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $orderData = [
            'order_type' => $data['order_type'],
            'reservation_id' => $data['reservation_id'] ?? null,
            'customer_id' => $data['customer_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'contact_phone' => $data['contact_phone'] ?? null,
            'user_id' => Auth::id(),
            'order_time' => now(),
            'status' => 'pending',
            'order_code' => 'ORD' . Str::upper(Str::random(8)),
        ];

        $items = [];
        $totalAmount = 0;

        if (!empty($data['items'])) {
            // Lấy danh sách dish và combo ID
            $dishIds = collect($data['items'])->pluck('dish_id')->filter()->unique()->toArray();
            $comboIds = collect($data['items'])->pluck('combo_id')->filter()->unique()->toArray();

            // Lấy dữ liệu dish và combo
            $menuItems = Dish::whereIn('id', $dishIds)->get()->keyBy('id');
            $comboItems = Combo::whereIn('id', $comboIds)->get()->keyBy('id');

            foreach ($data['items'] as $item) {
                $quantity = max(1, (int)($item['quantity'] ?? 1));

                if (!empty($item['dish_id'])) {
                    $menuItem = $menuItems->get($item['dish_id']);
                    if (!$menuItem) {
                        $result->setMessage("Món ăn ID {$item['dish_id']} không tồn tại");
                        return $result;
                    }

                    $lineTotal = $menuItem->selling_price * $quantity;
                    $totalAmount += $lineTotal;

                    $items[] = [
                        'dish_id' => $item['dish_id'],
                        'combo_id' => null,
                        'quantity' => $quantity,
                        'unit_price' => $menuItem->selling_price,
                        'kitchen_status' => $item['kitchen_status'] ?? 'pending',
                        'notes' => $item['notes'] ?? null,
                        'is_priority' => $item['is_priority'] ?? false,
                        'is_additional' => $item['is_additional'] ?? false,
                        'item_name' => $menuItem->name,
                    ];
                } elseif (!empty($item['combo_id'])) {
                    $comboItem = $comboItems->get($item['combo_id']);
                    if (!$comboItem) {
                        $result->setMessage("Combo ID {$item['combo_id']} không tồn tại");
                        return $result;
                    }

                    $lineTotal = $comboItem->selling_price * $quantity;
                    $totalAmount += $lineTotal;

                    $items[] = [
                        'dish_id' => null,
                        'combo_id' => $item['combo_id'],
                        'quantity' => $quantity,
                        'unit_price' => $comboItem->selling_price,
                        'kitchen_status' => $item['kitchen_status'] ?? 'pending',
                        'notes' => $item['notes'] ?? null,
                        'is_priority' => $item['is_priority'] ?? false,
                        'is_additional' => $item['is_additional'] ?? false,
                        'item_name' => $comboItem->name,
                    ];
                } else {
                    $result->setMessage("Mỗi item phải có dish_id hoặc combo_id");
                    return $result;
                }
            }
        }

        $orderData['total_amount'] = $totalAmount;
        $orderData['final_amount'] = $totalAmount;

        $tables = $data['tables'] ?? [];

        $order = $this->orderRepository->createOrder($orderData, $items, $tables);

        if (!$order) {
            $result->setMessage('Tạo đơn hàng thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Tạo đơn hàng thành công!');
        return $result;
    }

    public function getOrderDetail(string $id): DataAggregate
    {
        $result = new DataAggregate();

        $order = $this->orderRepository->getByConditions(['id' => $id]);
        if (!$order) {
            $result->setMessage(message: 'Đơn hàng không tồn tại');
            return $result;
        }

        $order->load(['tables', 'reservation', 'customer', 'user', 'items.menuItem', 'items.combo', 'bill']);
        $data = [
            'id' => (string)$order->id,
            'order_code' => $order->order_code,
            'order_type' => $order->order_type,
            'tables' => $order->tables->map(function ($table) {
                return [
                    'id' => (string)$table->id,
                    'table_number' => $table->table_number,
                ];
            })->toArray(),

            'reservation' => $order->reservation ? [
                'id' => (string)$order->reservation->id,
                'reservation_time' => $order->reservation->reservation_time,
                'table_id' => (string)$order->reservation->table_id,
            ] : null,
            'customer' => $order->customer ? [
                'id' => (string)$order->customer->id,
                'full_name' => $order->customer->full_name,
                'phone_number' => $order->customer->phone_number,
            ] : null,
            'user' => $order->user ? [
                'id' => (string)$order->user->id,
                'name' => $order->user->name,
            ] : null,
            'status' => $order->status,
            'notes' => $order->notes,
            'delivery_address' => $order->delivery_address,
            'contact_name' => $order->contact_name,
            'contact_email' => $order->contact_email,
            'contact_phone' => $order->contact_phone,
            'total_amount' => $order->total_amount,
            'final_amount' => $order->final_amount,
            'order_time' => $order->order_time,
            'delivered_at' => $order->delivered_at,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => (string)$item->id,
                    'dish_id' => $item->menuItem ? [
                        'id' => (string)$item->menuItem->id,
                        'name' => $item->menuItem->name,
                    ] : null,
                    'combo_id' => $item->combo ? [
                        'id' => (string)$item->combo->id,
                        'combo_name' => $item->combo->name,
                    ] : null,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'notes' => $item->notes,
                    'kitchen_status' => $item->kitchen_status,
                    'is_additional' => $item->is_additional,
                ];
            })->toArray(),
            'bill' => $order->bill ? [
                'id' => (string)$order->bill->id,
                'final_amount' => $order->bill->final_amount,
            ] : null,
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ];

        $result->setResultSuccess(data: ['order' => $data]);
        return $result;
    }

    public function updateOrder(array $data, $id): DataAggregate
    {
        $result = new DataAggregate();
        $order = $this->orderRepository->getByConditions(['id' => $id]);
        if (!$order) {
            $result->setMessage(message: 'Đơn hàng không tồn tại');
            return $result;
        }
        $orderData = [
            'order_type' => $data['order_type'] ?? $order->order_type,
            'reservation_id' => $data['reservation_id'] ?? $order->reservation_id,
            'customer_id' => $data['customer_id'] ?? $order->customer_id,
            'status' => $data['status'] ?? $order->status,
            'notes' => $data['notes'] ?? $order->notes,
            'delivery_address' => $data['delivery_address'] ?? $order->delivery_address,
            'contact_name' => $data['contact_name'] ?? $order->contact_name,
            'contact_email' => $data['contact_email'] ?? $order->contact_email,
            'contact_phone' => $data['contact_phone'] ?? $order->contact_phone,
        ];
        $items = [];
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $items[] = [
                    'id' => isset($item['id']) ? (int)$item['id'] : null,
                    'dish_id' => isset($item['dish_id']) ? (int)$item['dish_id'] : null,
                    'combo_id' => isset($item['combo_id']) ? (int)$item['combo_id'] : null,
                    'quantity' => (int)$item['quantity'],
                    'kitchen_status' => $item['kitchen_status'] ?? 'pending',
                    'notes' => $item['notes'] ?? null,
                    'is_priority' => isset($item['is_priority']) ? (int)$item['is_priority'] : 0,
                    'is_additional' => isset($item['is_additional']) ? (int)$item['is_additional'] : 0,
                ];
            }
        }

        $tables = $data['tables'] ?? [];

        $updatedOrder = $this->orderRepository->updateOrder($order, $orderData, $items, $tables);

        if (!$updatedOrder) {
            $result->setMessage('Cập nhật đơn hàng thất bại, vui lòng thử lại!');
            return $result;
        }

        // Lấy lại danh sách món mới nhất
        $orderItems = $updatedOrder->items;
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                // Nếu là món bị xóa (quantity <= 0 và có id)
                if (isset($item['id']) && isset($item['quantity']) && $item['quantity'] <= 0) {
                    // Lấy thông tin món vừa xóa từ $order hoặc $order->items trước khi update
                    $deletedItem = null;
                    $oldOrderItems = $order->items ?? collect();
                    $deletedItem = $oldOrderItems->where('id', $item['id'])->first();
                    if ($deletedItem) {
                        event(new \App\Events\Orders\OrderItemDeleted([
                            'id' => $deletedItem->id,
                            'order_id' => $deletedItem->order_id,
                            'item_name' => $deletedItem->menuItem->name ?? $deletedItem->combo->name ?? '',
                            'status' => $deletedItem->kitchen_status,
                            'quantity' => $deletedItem->quantity,
                            'notes' => $deletedItem->notes,
                            'is_additional' => $deletedItem->is_additional,
                        ]));
                    }
                } else if (empty($item['id'])) {
                    // Nếu là món mới (không có id trước đó, nhưng đã được tạo ra)
                    $createdItem = $orderItems->where('dish_id', $item['dish_id'] ?? null)
                        ->where('combo_id', $item['combo_id'] ?? null)
                        ->where('quantity', $item['quantity'])
                        ->where('is_additional', $item['is_additional'] ?? false)
                        ->sortByDesc('id')->first();
                    if ($createdItem) {
                        event(new \App\Events\Orders\OrderItemCreated([
                            'id' => $createdItem->id,
                            'order_id' => $createdItem->order_id,
                            'item_name' => $createdItem->menuItem->name ?? $createdItem->combo->name ?? '',
                            'status' => $createdItem->kitchen_status,
                            'quantity' => $createdItem->quantity,
                            'notes' => $createdItem->notes,
                            'is_additional' => $createdItem->is_additional,
                            'updated_at' => $createdItem->updated_at,
                        ]));
                    }
                } else {
                    // Nếu là món sửa (có id), broadcast event update
                    $updatedItem = $orderItems->where('id', $item['id'])->first();
                    if ($updatedItem) {
                        event(new \App\Events\Orders\OrderItemUpdated([
                            'id' => $updatedItem->id,
                            'order_id' => $updatedItem->order_id,
                            'item_name' => $updatedItem->menuItem->name ?? $updatedItem->combo->name ?? '',
                            'status' => $updatedItem->kitchen_status,
                            'quantity' => $updatedItem->quantity,
                            'notes' => $updatedItem->notes,
                            'is_additional' => $updatedItem->is_additional,
                            'updated_at' => $updatedItem->updated_at,
                        ]));
                    }
                }
            }
        }

        // Dispatch event OrderUpdated
        event(new \App\Events\Orders\OrderUpdated([
            'id' => $updatedOrder->id,
            'order_code' => $updatedOrder->order_code,
            'status' => $updatedOrder->status,
            'updated_at' => $updatedOrder->updated_at,
        ]));

        $result->setResultSuccess(message: 'Cập nhật đơn hàng thành công!');
        return $result;
    }

    public function listTrashedOrders(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;
        $pagination = $this->orderRepository->getTrashOrderList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'order_code' => $item->order_code,
                'order_type' => $item->order_type,
                'table' => $item->table ? [
                    'id' => (string)$item->table->id,
                    'name' => $item->table->name,
                ] : null,
                'reservation' => $item->reservation ? [
                    'id' => (string)$item->reservation->id,
                    'reservation_time' => $item->reservation->reservation_time,
                ] : null,
                'customer' => $item->customer ? [
                    'id' => (string)$item->customer->id,
                    'full_name' => $item->customer->full_name,
                    'phone_number' => $item->customer->phone_number,
                ] : null,
                'user' => $item->user ? [
                    'id' => (string)$item->user->id,
                    'name' => $item->user->name,
                ] : null,
                'status' => $item->status,
                'notes' => $item->notes,
                'delivery_address' => $item->delivery_address,
                'contact_name' => $item->contact_name,
                'contact_email' => $item->contact_email,
                'contact_phone' => $item->contact_phone,
                'total_amount' => $item->total_amount,
                'final_amount' => $item->final_amount,
                'order_time' => $item->order_time,
                'delivered_at' => $item->delivered_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'deleted_at' => $item->deleted_at,
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }

    public function softDeleteOrder(string $id): DataAggregate
    {
        $result = new DataAggregate();
        $order = $this->orderRepository->getByConditions(['id' => $id]);
        if (!$order) {
            $result->setMessage(message: 'Đơn hàng không tồn tại');
            return $result;
        }
        $ok = $this->orderRepository->softDeleteOrder($id);
        if (!$ok) {
            $result->setMessage(message: 'Xóa đơn hàng thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa đơn hàng thành công!');
        return $result;
    }

    public function forceDeleteOrder(string $id): DataAggregate
    {
        $result = new DataAggregate();
        $ok = $this->orderRepository->forceDeleteOrder($id);
        if (!$ok) {
            $result->setMessage(message: 'Xóa vĩnh viễn đơn hàng thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Xóa vĩnh viễn đơn hàng thành công!');
        return $result;
    }

    public function restoreOrder(string $id): DataAggregate
    {
        $result = new DataAggregate();
        $ok = $this->orderRepository->restoreOrder($id);
        if (!$ok) {
            $result->setMessage(message: 'Khôi phục đơn hàng thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(message: 'Khôi phục đơn hàng thành công!');
        return $result;
    }

    public function updateOrderItemStatus(int $orderItemId, string $status): DataAggregate
    {
        $result = new DataAggregate();

        $validStatuses = ['pending', 'preparing', 'ready', 'served', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $result->setMessage(message: 'Trạng thái món không hợp lệ');
            return $result;
        }

        $ok = $this->orderRepository->updateItemStatus($orderItemId, $status, Auth::id());
        if (!$ok->isSuccessCode()) {
            $result->setMessage(message: 'Cập nhật trạng thái món thất bại, vui lòng thử lại!');
            return $result;
        }
        $result->setResultSuccess(data: $ok->getData(), message: 'Cập nhật trạng thái món thành công!');
        return $result;
    }

    public function countByStatus(): array
    {
        $listStatus = [
            'pending',
            'confirmed',
            'preparing',
            'ready',
            'served',
            'delivering',
            'completed',
            'cancelled'
        ];
        $counts = [];

        foreach ($listStatus as $status) {
            $counts[$status] = $this->orderRepository->countByConditions(['status' => $status]);
        }
        return $counts;
    }

    public function getOrderByTableId($tableId): DataAggregate
    {
        $result = new DataAggregate();
        $order = $this->orderRepository->getOrderByTableId($tableId);
        if (!$order) {
            $result->setResultError(message: 'Không tìm thấy đơn hàng cho bàn này');
            return $result;
        }
        $result->setResultSuccess(data: ['order' => $order]);
        return $result;
    }
}
