<?php

namespace App\Services\Order;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\Combo;
use App\Models\Dish;
use App\Models\Order;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;


class OrderService
{
    protected \App\Repositories\Order\OrderChangeLogRepositoryInterface $orderChangeLogRepository;
    protected OrderRepositoryInterface $orderRepository;
    protected NotificationService $notificationService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        NotificationService $notificationService,
        \App\Repositories\Order\OrderChangeLogRepositoryInterface $orderChangeLogRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->notificationService = $notificationService;
        $this->orderChangeLogRepository = $orderChangeLogRepository;
    }

    public function getListOrders(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? min((int)$params['limit'], 1000) : 100;
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
                'user_name' => $item->user ? ($item->user->username ?: $item->user->full_name) : null,
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
            // Theo yêu cầu: đơn hàng mới tạo sẽ ở trạng thái "đã xác nhận"
            'status' => 'confirmed',
            'order_code' => 'ORD' . str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT),
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

        // Tạo thông báo cho đơn hàng mới
        // $orderNotificationData = [
        //     'id' => $order->id,
        //     'order_code' => $order->order_code,
        //     'total_amount' => $order->total_amount,
        //     'reservation_id' => $order->reservation_id,
        // ];
        // $this->notificationService->createOrderNotification($orderNotificationData);

        // Fire event OrderCreated để trigger listener và broadcast
        event(new \App\Events\Orders\OrderCreated([
            'id' => $order->id,
            'order_code' => $order->order_code,
            'created_at' => $order->created_at,
            'status' => $order->status,
            'customer_name' => $order->customer->full_name ?? null,
        ]));

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
                    'is_priority' => $item->is_priority,
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
        $batchId = uniqid('batch_');
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
            'total_amount' => $data['total_amount'] ?? $order->total_amount,
            'final_amount' => $data['final_amount'] ?? $order->final_amount,
        ];

        // Kiểm tra nếu có thay đổi trạng thái
        $previousStatus = $order->status;
        $newStatus = $data['status'] ?? $order->status;
        $statusChanged = $previousStatus !== $newStatus;

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

        // Tạo thông báo nếu có thay đổi trạng thái
        // if ($statusChanged) {
        //     $orderNotificationData = [
        //         'id' => $updatedOrder->id,
        //         'order_code' => $updatedOrder->order_code,
        //         'reservation_id' => $updatedOrder->reservation_id,
        //     ];
        //     $this->notificationService->createOrderStatusNotification($orderNotificationData, $previousStatus, $newStatus);
        // }

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
                        // GHI LOG XOÁ MÓN
                        $this->orderChangeLogRepository->createOrderChangeLog([
                            'batch_id' => $batchId,
                            'order_id' => $updatedOrder->id,
                            'user_id' => Auth::id(),
                            'change_timestamp' => now(),
                            'change_type' => 'DELETE_ITEM',
                            'field_changed' => 'item',
                            'old_value' => json_encode([
                                'id' => $deletedItem->id,
                                'dish_id' => $deletedItem->dish_id,
                                'combo_id' => $deletedItem->combo_id,
                                'quantity' => $deletedItem->quantity,
                                'notes' => $deletedItem->notes,
                            ]),
                            'new_value' => null,
                            'description' => 'Xóa món khỏi đơn hàng',
                        ]);
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
                        // GHI LOG CẬP NHẬT MÓN
                        $oldItem = ($order->items ?? collect())->where('id', $item['id'])->first();
                        if ($oldItem) {
                            $fieldsItem = ['quantity', 'notes', 'is_priority', 'is_additional'];
                            foreach ($fieldsItem as $f) {
                                if ($oldItem->$f != $updatedItem->$f) {
                                    $this->orderChangeLogRepository->createOrderChangeLog([
                                        'batch_id' => $batchId,
                                        'order_id' => $updatedOrder->id,
                                        'user_id' => Auth::id(),
                                        'change_timestamp' => now(),
                                        'change_type' => 'UPDATE_ITEM',
                                        'field_changed' => 'item.' . $f,
                                        'old_value' => $oldItem->$f,
                                        'new_value' => $updatedItem->$f,
                                        'description' => 'Cập nhật thuộc tính món: ' . $f,
                                    ]);
                                }
                            }
                        }
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

        // GHI LOG THAY ĐỔI ĐƠN HÀNG
        $userId = Auth::id();
        $now = now();
        $orderId = $updatedOrder->id;
        // 1. Log thay đổi trạng thái
        if ($statusChanged) {
            $this->orderChangeLogRepository->createOrderChangeLog([
                'batch_id' => $batchId,
                'order_id' => $orderId,
                'user_id' => $userId,
                'change_timestamp' => $now,
                'change_type' => 'UPDATE_STATUS',
                'field_changed' => 'status',
                'old_value' => $previousStatus,
                'new_value' => $newStatus,
                'description' => 'Cập nhật trạng thái đơn hàng',
            ]);
        }
        // 2. Log thay đổi các trường chính
        // Tự động log mọi thay đổi trường của đơn hàng (kể cả trường mới phát sinh)
        $ignoreFields = ['id', 'created_at', 'updated_at'];
        $allFields = array_keys((array)$order->getAttributes());
        foreach ($allFields as $field) {
            if (in_array($field, $ignoreFields)) continue;
            $oldValue = $order->$field;
            $newValue = $updatedOrder->$field;
            if ($oldValue != $newValue) {
                // Nếu là dạng object/array thì lưu JSON
                $isComplex = is_array($oldValue) || is_object($oldValue) || is_array($newValue) || is_object($newValue);
                $logOld = $isComplex ? json_encode($oldValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $oldValue;
                $logNew = $isComplex ? json_encode($newValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $newValue;
                $this->orderChangeLogRepository->createOrderChangeLog([
                    'batch_id' => $batchId,
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'change_timestamp' => $now,
                    'change_type' => 'UPDATE_FIELD',
                    'field_changed' => $field,
                    'old_value' => $logOld,
                    'new_value' => $logNew,
                    'description' => 'Cập nhật trường ' . $field,
                ]);
            }
        }
        // 2. Log thay đổi các trường chính
        // Tự động log mọi thay đổi trường của đơn hàng (kể cả trường mới phát sinh)
        $ignoreFields = ['id', 'created_at', 'updated_at'];
        $allFields = array_keys((array)$order->getAttributes());
        foreach ($allFields as $field) {
            if (in_array($field, $ignoreFields)) continue;
            $oldValue = $order->$field;
            $newValue = $updatedOrder->$field;
            if ($oldValue != $newValue) {
                // Nếu là dạng object/array thì lưu JSON
                $isComplex = is_array($oldValue) || is_object($oldValue) || is_array($newValue) || is_object($newValue);
                $logOld = $isComplex ? json_encode($oldValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $oldValue;
                $logNew = $isComplex ? json_encode($newValue, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : $newValue;
                $this->orderChangeLogRepository->createOrderChangeLog([
                    'batch_id' => $batchId,
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'change_timestamp' => $now,
                    'change_type' => 'UPDATE_FIELD',
                    'field_changed' => $field,
                    'old_value' => $logOld,
                    'new_value' => $logNew,
                    'description' => 'Cập nhật trường ' . $field,
                ]);
            }
        }
        // 3. Log thay đổi món ăn (thêm/xóa/sửa)
        // Xóa
        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                if (isset($item['id']) && isset($item['quantity']) && $item['quantity'] <= 0) {
                    $deletedItem = ($order->items ?? collect())->where('id', $item['id'])->first();
                    if ($deletedItem) {
                        $this->orderChangeLogRepository->createOrderChangeLog([
                            'batch_id' => $batchId,
                            'order_id' => $orderId,
                            'user_id' => $userId,
                            'change_timestamp' => $now,
                            'change_type' => 'DELETE_ITEM',
                            'field_changed' => 'item',
                            'old_value' => json_encode([
                                'id' => $deletedItem->id,
                                'dish_id' => $deletedItem->dish_id,
                                'combo_id' => $deletedItem->combo_id,
                                'quantity' => $deletedItem->quantity,
                                'notes' => $deletedItem->notes,
                            ]),
                            'new_value' => null,
                            'description' => 'Xóa món khỏi đơn hàng',
                        ]);
                    }
                } else if (empty($item['id'])) {
                    // Thêm mới
                    $createdItem = $updatedOrder->items->where('dish_id', $item['dish_id'] ?? null)
                        ->where('combo_id', $item['combo_id'] ?? null)
                        ->where('quantity', $item['quantity'])
                        ->where('is_additional', $item['is_additional'] ?? false)
                        ->sortByDesc('id')->first();
                    if ($createdItem) {
                        $this->orderChangeLogRepository->createOrderChangeLog([
                            'batch_id' => $batchId,
                            'order_id' => $orderId,
                            'user_id' => $userId,
                            'change_timestamp' => $now,
                            'change_type' => 'ADD_ITEM',
                            'field_changed' => 'item',
                            'old_value' => null,
                            'new_value' => json_encode([
                                'id' => $createdItem->id,
                                'dish_id' => $createdItem->dish_id,
                                'combo_id' => $createdItem->combo_id,
                                'quantity' => $createdItem->quantity,
                                'notes' => $createdItem->notes,
                            ]),
                            'description' => 'Thêm món vào đơn hàng',
                        ]);
                    }
                } else {
                    // Sửa món
                    $oldItem = ($order->items ?? collect())->where('id', $item['id'])->first();
                    $newItem = $updatedOrder->items->where('id', $item['id'])->first();
                    if ($oldItem && $newItem) {
                        // So sánh toàn bộ object món ăn, log chi tiết nếu có thay đổi
                        $fieldsItem = ['dish_id', 'combo_id', 'quantity', 'notes', 'is_priority', 'is_additional', 'kitchen_status'];
                        $changed = false;
                        $diffs = [];
                        foreach ($fieldsItem as $f) {
                            if ($oldItem->$f != $newItem->$f) {
                                $changed = true;
                                $diffs[$f] = [
                                    'old' => $oldItem->$f,
                                    'new' => $newItem->$f
                                ];
                            }
                        }
                        if ($changed) {
                            $this->orderChangeLogRepository->createOrderChangeLog([
                                'batch_id' => $batchId,
                                'order_id' => $orderId,
                                'user_id' => $userId,
                                'change_timestamp' => $now,
                                'change_type' => 'UPDATE_ITEM',
                                'field_changed' => 'item',
                                'old_value' => json_encode($oldItem, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                                'new_value' => json_encode($newItem, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                                'description' => 'Cập nhật món ăn: ' . ($newItem->menuItem->name ?? $newItem->combo->name ?? ''),
                            ]);
                        }
                    }
                }
            }
        }
        // 4. Log thay đổi bàn (nếu có)
        if (!empty($data['tables'])) {
            $oldTableIds = ($order->tables ?? collect())->pluck('id')->toArray();
            $newTableIds = ($updatedOrder->tables ?? collect())->pluck('id')->toArray();
            if ($oldTableIds !== $newTableIds) {
                $this->orderChangeLogRepository->createOrderChangeLog([
                    'batch_id' => $batchId,
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'change_timestamp' => $now,
                    'change_type' => 'UPDATE_TABLES',
                    'field_changed' => 'tables',
                    'old_value' => json_encode($oldTableIds),
                    'new_value' => json_encode($newTableIds),
                    'description' => 'Cập nhật danh sách bàn',
                ]);
            }
        }
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
        $batchId = uniqid('batch_');
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

        // Tạo thông báo cho việc cập nhật trạng thái món ăn
        // $orderItem = $ok->getData();
        // if ($orderItem) {
        //     $kitchenOrderData = [
        //         'id' => $orderItem->id,
        //         'order_id' => $orderItem->order_id,
        //         'item_name' => $orderItem->menuItem->name ?? $orderItem->combo->name ?? 'Món ăn',
        //         'quantity' => $orderItem->quantity,
        //     ];
        //     $this->notificationService->createKitchenOrderStatusNotification($kitchenOrderData, $orderItem->kitchen_status, $status);
        // }

        // Ghi log thay đổi trạng thái món ăn trong đơn hàng
        $orderItem = $ok->getData();
        if ($orderItem) {
            $this->orderChangeLogRepository->createOrderChangeLog([
                'batch_id' => $batchId,
                'order_id' => $orderItem->order_id,
                'user_id' => Auth::id(),
                'change_timestamp' => now(),
                'change_type' => 'UPDATE_ITEM_STATUS',
                'field_changed' => 'item.kitchen_status',
                'old_value' => $orderItem->getOriginal('kitchen_status'),
                'new_value' => $orderItem->kitchen_status,
                'description' => 'Cập nhật trạng thái món ăn',
            ]);
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

    /**
     * Lấy lịch sử thay đổi của đơn hàng, trả về user_name (username nếu có, fallback full_name)
     */
    public function getOrderChangeLogs(int $orderId)
    {
        $logs = $this->orderChangeLogRepository->getOrderChangeLogs($orderId);
        $userIds = $logs->pluck('user_id')->filter()->unique()->toArray();
        $users = [];
        if (!empty($userIds)) {
            $users = \App\Models\User::whereIn('id', $userIds)
                ->get(['id', 'username', 'full_name'])
                ->keyBy('id')
                ->map(function ($user) {
                    return $user->username ?: $user->full_name;
                })
                ->toArray();
        }
        // Gom nhóm theo batch_id
        $batches = $logs->groupBy('batch_id')->map(function ($group) use ($users) {
            $firstLog = $group->first();
            return [
                'batch_id' => $firstLog->batch_id,
                'change_timestamp' => $group->max('change_timestamp'),
                'user_id' => $firstLog->user_id,
                'user_name' => $users[$firstLog->user_id] ?? null,
                'logs' => $group->sortBy('change_type')->sortByDesc('change_timestamp')->values()->toArray(),
            ];
        })->sortByDesc('change_timestamp')->values();
        return $batches;
    }

    /**
     * Lấy toàn bộ lịch sử thay đổi đơn hàng (cho trang quản trị)
     */
    public function getAllOrderChangeLogs()
    {
        $logs = $this->orderChangeLogRepository->getAllOrderChangeLogs();
        $userIds = $logs->pluck('user_id')->filter()->unique()->toArray();
        $users = [];
        if (!empty($userIds)) {
            $users = \App\Models\User::whereIn('id', $userIds)
                ->get(['id', 'username', 'full_name'])
                ->keyBy('id')
                ->map(function ($user) {
                    return $user->username ?: $user->full_name;
                })
                ->toArray();
        }
        $logs = $logs->map(function ($log) use ($users) {
            $log->user_name = $users[$log->user_id] ?? null;
            return $log;
        });
        return $logs;
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
