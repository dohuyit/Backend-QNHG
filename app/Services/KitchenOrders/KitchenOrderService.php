<?php

namespace App\Services\KitchenOrders;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\OrderItem;
use App\Repositories\KitchenOrders\KitchenOrderRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\Notifications\NotificationService;

class KitchenOrderService
{
    protected KitchenOrderRepositoryInterface $kitchenOrderRepository;
    protected OrderRepositoryInterface $orderRepository;
    protected NotificationService $notificationService;

    public function __construct(
        KitchenOrderRepositoryInterface $kitchenOrderRepository,
        OrderRepositoryInterface $orderRepository,
        NotificationService $notificationService
    ) {
        $this->orderRepository = $orderRepository;
        $this->kitchenOrderRepository = $kitchenOrderRepository;
        $this->notificationService = $notificationService;
    }

    public function getListKitchenOrder(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 1000;
        $pagination = $this->kitchenOrderRepository->getKitchenOrderList(filter: $filter, limit: $limit);

        // Eager-load trước các OrderItem liên quan để tránh N+1 và tính cooking_time chính xác
        $orderItemIds = collect($pagination->items())
            ->pluck('order_item_id')
            ->filter()
            ->unique()
            ->values();

        $orderItemsMap = OrderItem::with([
            'menuItem.category',                 // món lẻ -> category có cooking_time
            'combo.items.dish.category',         // combo -> các món con -> category có cooking_time
        ])->whereIn('id', $orderItemIds)->get()->keyBy('id');

        $data = [];
        foreach ($pagination->items() as $item) {
            $cookingTime = null;

            if ($item->order_item_id && isset($orderItemsMap[$item->order_item_id])) {
                $orderItem = $orderItemsMap[$item->order_item_id];

                if ($item->item_type === 'dish') {
                    // Món lẻ: lấy cooking_time theo category của dish
                    $cookingTime = optional(optional($orderItem->menuItem)->category)->cooking_time;
                } elseif ($item->item_type === 'combo') {
                    // Combo: ưu tiên lấy cooking_time của đúng món con (khớp theo tên item_name),
                    // nếu không khớp được thì fallback = max cooking_time trong combo
                    $combo = $orderItem->combo;
                    if ($combo && $combo->items) {
                        $normalizedKoName = $this->normalizeName((string) $item->item_name);
                        $matchedCookingTime = null;

                        foreach ($combo->items as $comboItem) {
                            $dish = $comboItem->dish;
                            if (!$dish) { continue; }
                            $dishName = (string) $dish->name;
                            $dishCookingTime = optional(optional($dish)->category)->cooking_time;

                            // Nếu tên khớp (sau normalize), dùng cooking_time của món khớp
                            $normalizedDishName = $this->normalizeName($dishName);
                            if ($dishName && (
                                $normalizedDishName === $normalizedKoName ||
                                str_contains($normalizedDishName, $normalizedKoName) ||
                                str_contains($normalizedKoName, $normalizedDishName)
                            )) {
                                if (is_numeric($dishCookingTime)) {
                                    $matchedCookingTime = (int) $dishCookingTime;
                                    break;
                                }
                            }
                        }

                        if (!is_null($matchedCookingTime) && $matchedCookingTime > 0) {
                            $cookingTime = $matchedCookingTime;
                        } else {
                            // Fallback: lấy max cooking_time của tất cả món trong combo
                            $times = [];
                            foreach ($combo->items as $comboItem) {
                                $t = optional(optional(optional($comboItem->dish)->category))->cooking_time;
                                if (is_numeric($t) && (int)$t > 0) { $times[] = (int) $t; }
                            }
                            if (!empty($times)) {
                                $cookingTime = max($times);
                            } else {
                                // Fallback cuối: cố gắng map theo tên món trong bảng dishes
                                $likeName = trim($item->item_name);
                                if ($likeName !== '') {
                                    $dishGuess = \App\Models\Dish::with('category')
                                        ->where('name', 'like', "%" . $likeName . "%")
                                        ->first();
                                    if ($dishGuess && $dishGuess->category && is_numeric($dishGuess->category->cooking_time)) {
                                        $cookingTime = (int) $dishGuess->category->cooking_time;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            // Lấy danh sách số bàn của đơn (nhiều bàn)
            $tableNumbers = [];
            if ($item->order && $item->order->tables) {
                $tableNumbers = $item->order->tables->pluck('table_number')->filter()->values()->toArray();
            }

            $data[] = [
                'id' => (string) $item->id,
                'order_item_id' => $item->order_item_id,
                'order_code' => $item->order->order_code ?? null,
                'table_numbers' => $tableNumbers,
                'item_name' => $item->item_name,
                'combo_name' => $item->combo_name,
                'quantity' => (int) $item->quantity,
                'notes' => $item->notes,
                'status' => $item->status,
                'is_priority' => (bool) $item->is_priority,
                'item_type' => $item->item_type,
                'received_at' => $item->received_at,
                'completed_at' => $item->completed_at,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'cooking_time' => is_null($cookingTime) ? null : (int) $cookingTime,
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

    /**
     * Chuẩn hoá tên để so sánh: hạ chữ, bỏ khoảng trắng thừa, bỏ nội dung trong ngoặc.
     */
    private function normalizeName(string $name): string
    {
        // Bỏ nội dung trong ngoặc tròn
        $name = preg_replace('/\([^\)]*\)/u', '', $name);
        // Gom khoảng trắng về 1 dấu cách
        $name = preg_replace('/\s+/u', ' ', $name);
        // Trim và hạ chữ
        $name = trim(mb_strtolower($name, 'UTF-8'));
        return $name;
    }

    public function updateStatus(int $id, string $newStatus): DataAggregate
    {
        $result = new DataAggregate();

        $kitchenOrder = $this->kitchenOrderRepository->getByConditions(['id' => $id]);
        if (!$kitchenOrder) {
            $result->setMessage('Đơn bếp không tồn tại');
            return $result;
        }

        $currentStatus = $kitchenOrder->status;

        // Validate trạng thái mới có hợp lệ không
        $validStatuses = ['pending', 'preparing', 'ready', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $result->setMessage('Trạng thái không hợp lệ');
            return $result;
        }

        // Kiểm tra logic chuyển trạng thái
        $allowedTransitions = [
            'pending' => ['preparing', 'cancelled'],
            'preparing' => ['ready', 'cancelled'],
            'ready' => [],
            'cancelled' => [],
        ];

        if (!in_array($newStatus, $allowedTransitions[$currentStatus])) {
            $result->setMessage("Không thể chuyển từ trạng thái '{$currentStatus}' sang '{$newStatus}'");
            return $result;
        }

        // Chuẩn bị dữ liệu cập nhật
        $updateData = ['status' => $newStatus];
        if ($newStatus === 'preparing' && $currentStatus === 'pending') {
            $updateData['received_at'] = now();
        } elseif ($newStatus === 'ready' && $currentStatus === 'preparing') {
            $updateData['completed_at'] = now();
        }

        // Cập nhật kitchen_order
        $updateSuccess = $this->kitchenOrderRepository->updateByConditions(['id' => $id], $updateData);

        if (!$updateSuccess) {
            $result->setMessage('Cập nhật trạng thái đơn bếp thất bại');
            return $result;
        }

        // Cập nhật trạng thái món ăn (order_item)
        $orderItemId = $kitchenOrder->order_item_id;
        if ($orderItemId) {
            // Kiểm tra nếu đây là item của combo (có nhiều kitchen_order cho một order_item)
            $allKitchenOrders = $this->kitchenOrderRepository->getAllKitchenOrdersByOrderItemId($orderItemId);
            $allInNewStatus = $allKitchenOrders->every(function ($ko) use ($newStatus) {
                return $ko->status === $newStatus;
            });

            if ($allInNewStatus) {
                $updateItemSuccess = $this->kitchenOrderRepository->updateOrderItemStatus($orderItemId, $newStatus);

                if (!$updateItemSuccess) {
                    $result->setMessage('Cập nhật trạng thái món trong đơn hàng thất bại');
                    return $result;
                }
            }

            // Kiểm tra nếu món ăn bị hủy, cần cập nhật lại total_amount và final_amount
            if ($newStatus === 'cancelled' && $allInNewStatus) {
                $orderItem = $this->orderRepository->getByConditionOrderItem(['id' => $orderItemId]);
                if ($orderItem && $orderItem->order_id) {
                    $orderId = $orderItem->order_id;
                    $this->recalculateOrderAmounts($orderId);
                }
            }

            // Kiểm tra nếu toàn bộ món trong đơn đã 'ready' thì cập nhật đơn hàng
            $orderItem = $this->orderRepository->getByConditionOrderItem(['id' => $orderItemId]);
            if ($orderItem && $orderItem->order_id) {
                $orderId = $orderItem->order_id;
                $allItemsReady = $this->kitchenOrderRepository->areAllItemsReadyInOrder($orderId);
                if ($allItemsReady) {
                    $this->orderRepository->updateByConditions(['id' => $orderId], [
                        'status' => 'ready',
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Tạo thông báo cho việc cập nhật trạng thái
        $kitchenOrderData = [
            'id' => $kitchenOrder->id,
            'order_id' => $kitchenOrder->order_id,
            'item_name' => $kitchenOrder->item_name,
            'quantity' => $kitchenOrder->quantity,
        ];
        $this->notificationService->createKitchenOrderStatusNotification($kitchenOrderData, $currentStatus, $newStatus);

        // Thành công
        // Lấy lại dữ liệu kitchenOrder mới nhất để broadcast
        $kitchenOrderFresh = $this->kitchenOrderRepository->getByConditions(['id' => $id]);

        // Event cập nhật OrderItem cũ
        event(new \App\Events\Orders\OrderItemUpdated([
            'id' => $kitchenOrderFresh->id,
            'order_id' => $kitchenOrderFresh->order_id,
            'item_name' => $kitchenOrderFresh->item_name,
            'status' => $kitchenOrderFresh->status,
            'updated_at' => $kitchenOrderFresh->updated_at,
        ]));

        $result->setResultSuccess(
            message: 'Chuyển trạng thái thành công',
            data: [
                'id' => $kitchenOrder->id,
                'status' => $newStatus,
                'previous_status' => $currentStatus
            ]
        );

        return $result;
    }

    public function countByStatus(): array
    {
        $listStatus = ['pending', 'preparing', 'ready', 'cancelled'];
        $counts = [];

        foreach ($listStatus as $status) {
            $counts[$status] = $this->kitchenOrderRepository->countByConditions(['status' => $status]);
        }
        return $counts;
    }
    public function createKitchenOrder(array $data): DataAggregate
    {
        $result = new DataAggregate();
        $kitchenOrder = $this->kitchenOrderRepository->create($data);
        if (!$kitchenOrder) {
            $result->setMessage('Tạo đơn bếp thất bại');
            return $result;
        }

        // Tạo thông báo cho đơn bếp mới
        $kitchenOrderData = [
            'id' => $kitchenOrder->id,
            'order_id' => $kitchenOrder->order_id,
            'item_name' => $kitchenOrder->item_name,
            'quantity' => $kitchenOrder->quantity,
        ];
        $this->notificationService->createKitchenOrderStatusNotification($kitchenOrderData, '', 'pending');

        // Broadcast event & notification
        event(new \App\Events\KitchenOrders\KitchenOrderCreated([
            'id' => $kitchenOrder->id,
            'order_id' => $kitchenOrder->order_id,
            'item_name' => $kitchenOrder->item_name,
            'quantity' => $kitchenOrder->quantity,
            'status' => $kitchenOrder->status,
        ]));

        $result->setResultSuccess(data: $kitchenOrder, message: 'Tạo đơn bếp thành công');
        return $result;
    }

    private function recalculateOrderAmounts(int $orderId): void
    {
        $result = new DataAggregate();
        $order = $this->orderRepository->getByConditions(['id' => $orderId]);
        if (!$order) {
            $result->setMessage('Đơn hàng không tồn tại');
            return;
        }

        // Lấy tất cả order_items không bị hủy trong đơn hàng
        $activeOrderItems = OrderItem::where('order_id', $orderId)
            ->where('kitchen_status', '!=', 'cancelled')
            ->get();


        $newTotalAmount = 0;
        foreach ($activeOrderItems as $item) {
            $newTotalAmount += $item->unit_price * $item->quantity;
        }

        $discountAmount = $order->discount_amount ?? 0;
        $taxAmount = $order->tax_amount ?? 0;
        $serviceFee = $order->service_fee ?? 0;

        $newFinalAmount = $newTotalAmount - $discountAmount + $taxAmount + $serviceFee;

        // Cập nhật đơn hàng
        $this->orderRepository->updateByConditions(['id' => $orderId], [
            'total_amount' => $newTotalAmount,
            'final_amount' => $newFinalAmount,
            'updated_at' => now(),
        ]);
    }
}