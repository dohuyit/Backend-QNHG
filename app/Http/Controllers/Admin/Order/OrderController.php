<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest\StoreOrderRequest;
use App\Http\Requests\OrderRequest\UpdateOrderRequest;
use App\Models\Order;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\Order\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected OrderService $orderService;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(
        OrderService $orderService,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderService = $orderService;
        $this->orderRepository = $orderRepository;
    }

    public function getListOrders()
    {
        $params = request()->only([
            'page',
            'limit',
            'order_type',
            'status',
            'payment_status',
            'date_from',
            'date_to',
            'customer_id',
            'user_id',
            'table_id',
            'reservation_id',
            'query'
        ]);

        $result = $this->orderService->getListOrders($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }

    public function createOrder(StoreOrderRequest $request)
    {
        try {
            $data = $request->only([
                'order_type',
                'reservation_id',
                'customer_id',
                'notes',
                'delivery_address',
                'contact_name',
                'contact_email',
                'contact_phone',
                'items',
                'tables'
            ]);

            $result = $this->orderService->createOrder($data);
            if (!$result->isSuccessCode()) {
                return $this->responseFail(message: $result->getMessage());
            }
            return $this->responseSuccess(message: $result->getMessage());
        } catch (\Exception $e) {
            Log::error('Failed to create order: ' . $e->getMessage());
            return $this->responseFail(message: 'Có lỗi xảy ra khi tạo đơn hàng', statusCode: 500);
        }
    }

    public function getOrderDetail(string $id)
    {
        $result = $this->orderService->getOrderDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }

    public function updateOrder(UpdateOrderRequest $request, string $id)
    {
        $order = $this->orderRepository->getByConditions(['id' => $id]);
        if (!$order) {
            return $this->responseFail(message: 'Đơn hàng không tồn tại', statusCode: 404);
        }

        $data = $request->only([
            'order_type',
            'reservation_id',
            'customer_id',
            'notes',
            'delivery_address',
            'contact_name',
            'contact_email',
            'contact_phone',
            'items',
            'tables'
        ]);

        $result = $this->orderService->updateOrder($data, $order);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function listTrashedOrders(Request $request)
    {
        $params = $request->only([
            'page',
            'limit',
            'query',
            'order_type',
            'status',
            'payment_status',
            'date_from',
            'date_to',
            'customer_id',
            'user_id',
            'table_id',
            'reservation_id',
        ]);

        $result = $this->orderService->listTrashedOrders($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }

    public function softDeleteOrder(string $id)
    {
        $order = $this->orderRepository->getByConditions(['id' => $id]);
        if (!$order) {
            return $this->responseFail(message: 'Đơn hàng không tồn tại', statusCode: 404);
        }

        $result = $this->orderService->softDeleteOrder($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function forceDeleteOrder(string $id)
    {
        $result = $this->orderService->forceDeleteOrder($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function restoreOrder(string $id)
    {
        $result = $this->orderService->restoreOrder($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function updateItemStatus(Request $request, int $orderItemId)
    {
        $data = $request->only(['status']);
        if (!$data['status']) {
            return $this->responseFail(message: 'Trạng thái món không được để trống', statusCode: 422);
        }

        $result = $this->orderService->updateOrderItemStatus($orderItemId, $data['status']);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(data: $result->getData(), message: $result->getMessage());
    }

    public function countByStatus()
    {
        $result = $this->orderService->countByStatus();
        return $this->responseSuccess($result);
    }
}
