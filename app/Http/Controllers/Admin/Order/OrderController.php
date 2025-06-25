<?php

namespace App\Http\Controllers\Admin\Order;

use App\Http\Controllers\Controller;
use App\Services\Order\OrderService;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Helpers\ResponseHelper;
use App\Helpers\ErrorHelper;
use App\Models\Order;
use Illuminate\Http\Request;

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

    public function getListOrders(Request $request)
    {
        $params = $request->only([
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
            'search'
        ]);

        $result = $this->orderService->getListOrders($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function getOrderDetail(string $id)
    {
        $result = $this->orderService->getOrderDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                statusCode: 404
            );
        }
        $data = $result->getData();

        return $this->responseSuccess($data);
    }

    public function createOrder(Request $request)
    {
        $result = $this->orderService->createOrder($request->all());

        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess(
            data: $result->getData(),
            message: 'Tạo đơn hàng thành công'
        );
    }

    public function updateOrder(Request $request, string $id)
    {
        $order = $this->orderRepository->getByConditions(['id' => $id]);
        if (!$order) {
            return $this->responseFail(
                message: 'Đơn hàng không tồn tại',
                statusCode: 404
            );
        }

        $result = $this->orderService->updateOrder($request->all(), $order);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess(
            data: $result->getData(),
            message: 'Cập nhật đơn hàng thành công'
        );
    }

    public function updateItemStatus(Request $request, int $orderItemId)
    {
        $status = $request->input('status');
        if (!$status) {
            return $this->responseFail(
                message: 'Trạng thái món không được để trống',
                errors: [],
                statusCode: ErrorHelper::INVALID_REQUEST_FORMAT
            );
        }

        $result = $this->orderService->updateOrderItemStatus($orderItemId, $status);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess(
            data: $result->getData(),
            message: 'Cập nhật trạng thái món thành công'
        );
    }

    public function splitOrder(Request $request, int $orderId)
    {
        $items = $request->input('items');
        if (!$items || !is_array($items)) {
            return $this->responseFail(
                message: 'Danh sách món cần tách không hợp lệ',
                errors: [],
                statusCode: ErrorHelper::INVALID_REQUEST_FORMAT
            );
        }

        $result = $this->orderService->splitOrder($orderId, $items);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess(
            data: $result->getData(),
            message: 'Tách đơn hàng thành công'
        );
    }

    public function mergeOrders(Request $request)
    {
        $orderIds = $request->input('order_ids');
        if (!$orderIds || !is_array($orderIds) || count($orderIds) < 2) {
            return $this->responseFail(
                message: 'Danh sách đơn hàng cần gộp không hợp lệ',
                errors: [],
                statusCode: ErrorHelper::INVALID_REQUEST_FORMAT
            );
        }

        $result = $this->orderService->mergeOrders($orderIds);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess(
            data: $result->getData(),
            message: 'Gộp đơn hàng thành công'
        );
    }

    public function getOrderItemHistory(int $orderItemId)
    {
        $result = $this->orderService->getOrderItemHistory($orderItemId);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess($result->getData());
    }

    public function trackOrder(string $orderCode)
    {
        $result = $this->orderService->trackOrder($orderCode);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess($result->getData());
    }

    public function addOrderItem(Request $request, string $orderId)
    {
        $order = $this->orderRepository->getByConditions(['id' => $orderId]);
        if (!$order) {
            return $this->responseFail(
                message: 'Đơn hàng không tồn tại',
                statusCode: 404
            );
        }

        $result = $this->orderService->addOrderItem($orderId, $request->all());
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess(
            data: $result->getData(),
            message: 'Thêm món thành công'
        );
    }

    public function updateOrderItem(Request $request, string $orderId, int $itemId)
    {
        $order = $this->orderRepository->getByConditions(['id' => $orderId]);
        if (!$order) {
            return $this->responseFail(
                message: 'Đơn hàng không tồn tại',
                statusCode: 404
            );
        }

        $result = $this->orderService->updateOrderItem($orderId, $itemId, $request->all());
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess(
            data: $result->getData(),
            message: 'Cập nhật món thành công'
        );
    }

    public function deleteOrderItem(string $orderId, int $itemId)
    {
        $order = $this->orderRepository->getByConditions(['id' => $orderId]);
        if (!$order) {
            return $this->responseFail(
                message: 'Đơn hàng không tồn tại',
                statusCode: 404
            );
        }

        $result = $this->orderService->deleteOrderItem($orderId, $itemId);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }

        return $this->responseSuccess(
            message: 'Xóa món thành công'
        );
    }

    public function countByStatus()
    {
        $result = $this->orderService->countByStatus();

        return $this->responseSuccess($result);
    }
}
