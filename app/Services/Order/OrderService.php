<?php

namespace App\Services\Order;

use App\Repositories\Order\OrderRepositoryInterface;
use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Helpers\ErrorHelper;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Models\OrderItem;

class OrderService
{
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getListOrders(array $params = []): ListAggregate
    {
        $page = $params['page'] ?? 1;
        $limit = $params['limit'] ?? 10;
        $filters = array_diff_key($params, array_flip(['page', 'limit']));

        $pagination = $this->orderRepository->getListOrders($filters, $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'order_code' => $item->order_code,
                'order_type' => $item->order_type,
                'status' => $item->status,
                'payment_status' => $item->payment_status,
                'total_amount' => $item->total_amount,
                'final_amount' => $item->final_amount,
                'order_time' => $item->order_time,
                'customer' => $item->customer ? [
                    'id' => $item->customer->id,
                    'full_name' => $item->customer->full_name,
                    'phone_number' => $item->customer->phone_number
                ] : null,
                'created_at' => $item->created_at->toDateTimeString(),
                'updated_at' => $item->updated_at->toDateTimeString(),
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

    public function getOrderDetail(string $id): DataAggregate
    {
        $result = new DataAggregate;
        $order = $this->orderRepository->getOrderDetail($id);
        if (! $order) {
            $result->setResultError(message: 'Đơn hàng bạn tìm không hợp lệ hoặc đã bị xóa');
            return $result;
        }

        $result->setResultSuccess(data: ['order' => $order]);
        return $result;
    }

    public function updateOrder(array $data, Order $order): DataAggregate
    {
        // Validate trạng thái mới nếu có
        if (isset($data['status'])) {
            $currentStatus = $order->status;
            if (!in_array($data['status'], ['pending_confirmation', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled'])) {
                $result = new DataAggregate();
                $result->setResultError('Trạng thái đơn hàng không hợp lệ', [], ErrorHelper::INVALID_REQUEST_FORMAT);
                return $result;
            }
        }

        return $this->orderRepository->updateOrder($order->id, $data);
    }

    public function updateOrderItemStatus(int $orderItemId, string $status): DataAggregate
    {
        // Validate trạng thái mới
        $validStatuses = ['pending', 'preparing', 'ready', 'served', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            $result = new DataAggregate();
            $result->setResultError('Trạng thái món không hợp lệ', [], ErrorHelper::INVALID_REQUEST_FORMAT);
            return $result;
        }

        return $this->orderRepository->updateItemStatus($orderItemId, $status, Auth::id());
    }

    public function splitOrder(int $orderId, array $items): DataAggregate
    {
        try {
            // Lấy thông tin đơn hàng gốc
            $originalOrder = $this->orderRepository->getOrderDetail($orderId);
            if (!$originalOrder->isSuccessCode()) {
                return $originalOrder;
            }

            $orderData = $originalOrder->getData()['order'];

            // Tạo đơn hàng mới
            $newOrderData = [
                'order_type' => $orderData->order_type,
                'user_id' => Auth::id(),
                'customer_id' => $orderData->customer_id,
                'order_time' => now(),
                'status' => 'pending_confirmation',
                'payment_status' => 'unpaid',
                'items' => $items
            ];

            // Nếu là đơn tại bàn, copy thông tin bàn
            if ($orderData->order_type === 'dine-in') {
                $newOrderData['tables'] = $orderData->tables->toArray();
            }

            // Tạo đơn mới
            $newOrder = $this->orderRepository->createOrder($newOrderData);
            if (!$newOrder->isSuccessCode()) {
                return $newOrder;
            }

            // Cập nhật đơn cũ
            $remainingItems = $orderData->items->whereNotIn('id', collect($items)->pluck('id'))->toArray();
            $updateData = ['items' => $remainingItems];

            return $this->orderRepository->updateOrder($orderId, $updateData);

        } catch (\Exception $e) {
            $result = new DataAggregate();
            $result->setResultError('Không thể tách đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function mergeOrders(array $orderIds): DataAggregate
    {
        try {
            // Lấy thông tin đơn hàng đầu tiên làm đơn chính
            $mainOrder = $this->orderRepository->getOrderDetail($orderIds[0]);
            if (!$mainOrder->isSuccessCode()) {
                return $mainOrder;
            }

            $mainOrderData = $mainOrder->getData()['order'];
            $allItems = $mainOrderData->items->toArray();

            // Gộp items từ các đơn khác
            for ($i = 1; $i < count($orderIds); $i++) {
                $subOrder = $this->orderRepository->getOrderDetail($orderIds[$i]);
                if (!$subOrder->isSuccessCode()) {
                    return $subOrder;
                }

                $allItems = array_merge($allItems, $subOrder->getData()['order']->items->toArray());
            }

            // Cập nhật đơn chính
            $updateData = ['items' => $allItems];
            $result = $this->orderRepository->updateOrder($orderIds[0], $updateData);
            if (!$result->isSuccessCode()) {
                return $result;
            }

            // Xóa các đơn phụ
            foreach (array_slice($orderIds, 1) as $orderId) {
                $this->orderRepository->updateOrder($orderId, ['status' => 'cancelled']);
            }

            return $result;

        } catch (\Exception $e) {
            $result = new DataAggregate();
            $result->setResultError('Không thể gộp đơn hàng', ['error' => $e->getMessage()]);
            return $result;
        }
    }

    public function trackOrder(string $orderCode): DataAggregate
    {
        $result = new DataAggregate;
        $order = $this->orderRepository->getByConditions(['order_code' => $orderCode]);

        if (!$order) {
            $result->setResultError('Không tìm thấy đơn hàng');
            return $result;
        }

        $data = [
            'order_code' => $order->order_code,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'items' => $order->items->map(function($item) {
                return [
                    'name' => $item->menuItem->name,
                    'quantity' => $item->quantity,
                    'status' => $item->kitchen_status
                ];
            }),
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at
        ];

        $result->setResultSuccess($data);
        return $result;
    }

    public function getOrderItemHistory(int $orderItemId): DataAggregate
    {
        $result = new DataAggregate;
        $orderItem = $this->orderRepository->getOrderItem($orderItemId);

        if (!$orderItem) {
            $result->setResultError('Không tìm thấy món ăn');
            return $result;
        }

        $history = $orderItem->statusHistory()->orderBy('created_at', 'desc')->get();

        $data = [
            'item' => [
                'id' => $orderItem->id,
                'name' => $orderItem->menuItem->name,
                'quantity' => $orderItem->quantity
            ],
            'history' => $history->map(function($record) {
                return [
                    'status' => $record->status,
                    'changed_by' => $record->user->name,
                    'changed_at' => $record->created_at
                ];
            })
        ];

        $result->setResultSuccess($data);
        return $result;
    }

    public function addOrderItem(string $orderId, array $data): DataAggregate
    {
        // Validate dữ liệu
        if (!isset($data['menu_item_id']) || !isset($data['quantity'])) {
            $result = new DataAggregate();
            $result->setResultError('Thiếu thông tin món ăn', [], ErrorHelper::INVALID_REQUEST_FORMAT);
            return $result;
        }

        return $this->orderRepository->addOrderItem($orderId, $data);
    }

    public function createOrder(array $data): DataAggregate
    {
        // Validate dữ liệu đầu vào
        if (!isset($data['order_type']) || !in_array($data['order_type'], ['dine-in', 'takeaway', 'delivery'])) {
            $result = new DataAggregate();
            $result->setResultError('Loại đơn hàng không hợp lệ', [], ErrorHelper::INVALID_REQUEST_FORMAT);
            return $result;
        }

        // Tạo mã đơn hàng tự động
        $lastOrder = $this->orderRepository->getLastOrder();
        $orderNumber = $lastOrder ? (int)substr($lastOrder->order_code, 3) + 1 : 1;
        $data['order_code'] = 'ORD' . str_pad($orderNumber, 6, '0', STR_PAD_LEFT);

        // Thêm thông tin người tạo
        $data['user_id'] = Auth::id();
        $data['order_time'] = now();
        $data['status'] = 'pending_confirmation';
        $data['payment_status'] = 'unpaid';

        return $this->orderRepository->createOrder($data);
    }

    public function updateOrderItem(string $orderId, int $itemId, array $data): DataAggregate
    {
        // Validate dữ liệu
        if (empty($data)) {
            $result = new DataAggregate();
            $result->setResultError('Không có dữ liệu cập nhật', [], ErrorHelper::INVALID_REQUEST_FORMAT);
            return $result;
        }

        return $this->orderRepository->updateOrderItem($orderId, $itemId, $data);
    }

    public function deleteOrderItem(string $orderId, int $itemId): DataAggregate
    {
        return $this->orderRepository->deleteOrderItem($orderId, $itemId);
    }
}
