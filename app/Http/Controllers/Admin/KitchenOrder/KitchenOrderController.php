<?php

namespace App\Http\Controllers\Admin\KitchenOrder;

use App\Http\Controllers\Controller;
use App\Repositories\KitchenOrders\KitchenOrderRepositoryInterface;
use App\Services\KitchenOrders\KitchenOrderService;
use App\Http\Requests\KitchenOrderRequest\StoreKitchenOrderRequest;

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
        $request = request();
        $newStatus = $request->input('status');

        if (!$newStatus) {
            return $this->responseFail(message: 'Trạng thái mới không được cung cấp', statusCode: 400);
        }

        $result = $this->kitchenOrderService->updateStatus($id, $newStatus);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 400);
        }
        return $this->responseSuccess(message: $result->getMessage(), data: $result->getData());
    }
  public function countByStatus()
    {
        $filter = request()->all();

        $result = $this->kitchenOrderService->countByStatus($filter);

        return $this->responseSuccess($result);
    }
    public function store(StoreKitchenOrderRequest $request)
    {
        $data = $request->validated();
        $result = $this->kitchenOrderService->createKitchenOrder($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 400);
        }
        return $this->responseSuccess(message: $result->getMessage(), data: $result->getData());
    }
}
