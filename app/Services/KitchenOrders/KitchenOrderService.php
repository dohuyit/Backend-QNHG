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

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'order_item_id' => $item->order_item_id,
                'order_id' => $item->order_id,
                'table_numbers' => $item->table_numbers,
                'item_name' => $item->item_name,
                'quantity' => $item->quantity,
                'notes' => $item->notes,
                'status' => $item->status,
                'is_priority' => (bool) $item->is_priority,
                'received_at' => $item->received_at,
                'completed_at' => $item->completed_at,
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
            $updateItemSuccess = $this->kitchenOrderRepository->updateOrderItemStatus($orderItemId, $newStatus);

            if (!$updateItemSuccess) {
                $result->setMessage('Cập nhật trạng thái món trong đơn hàng thất bại');
                return $result;
            }

            // Kiểm tra nếu toàn bộ món trong đơn đã 'ready' thì cập nhật đơn hàng
            $orderItem = OrderItem::find($orderItemId);
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
        // Fire event realtime cho KitchenOrder cập nhật trạng thái
        // event(new \App\Events\KitchenOrders\KitchenOrderCreated([
        //     'id' => $kitchenOrderFresh->id,
        //     'order_id' => $kitchenOrderFresh->order_id,
        //     'item_name' => $kitchenOrderFresh->item_name,
        //     'status' => $kitchenOrderFresh->status,
        //     'updated_at' => $kitchenOrderFresh->updated_at,
        // ]));

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
}