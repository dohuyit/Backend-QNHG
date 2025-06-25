<?php

namespace App\Http\Controllers\Admin\KitchenOrder;

use App\Http\Controllers\Controller;
use App\Repositories\KitchenOrders\KitchenOrderRepositoryInterface;
use App\Services\KitchenOrders\KitchenOrderService;

class KitchenOrderController extends Controller
{
    protected KitchenOrderService $kitchenOrderService;
    protected KitchenOrderRepositoryInterface $kitchenOrderRepository;
    public function __construct(KitchenOrderService $kitchenOrderService, KitchenOrderRepositoryInterface $kitchenOrderRepository)
    {
        $this->kitchenOrderService = $kitchenOrderService;
        $this->kitchenOrderRepository = $kitchenOrderRepository;
    }
    public function getListKitchenOrders()
    {
        $params = request()->only([
            'page',
            'limit',
            'order_item_id',
            'order_id',
            'table_number',
            'item_name',
            'quantity',
            'notes',
            'status',
            'is_priority',
            'received_at',
            'completed_at',
        ]);
        $result = $this->kitchenOrderService->getListKitchenOrder($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function updateKitchenOrderStatus(int $id)
    {
        $result = $this->kitchenOrderService->updateStatus($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 400);
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function cancelKitchenOrder(int $id)
    {
        $result = $this->kitchenOrderService->cancelKitchenOrder($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 400);
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function countByStatus()
    {
        $result = $this->kitchenOrderService->countByStatus();

        return $this->responseSuccess($result);
    }
}
