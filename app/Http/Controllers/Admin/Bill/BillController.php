<?php

namespace App\Http\Controllers\Admin\Bill;

use App\Http\Controllers\Controller;
use App\Http\Requests\BillRequest\StoreBillRequest;
use App\Http\Requests\BillRequest\AddPaymentRequest;
use App\Http\Requests\BillRequest\UpdateBillRequest;
use App\Repositories\Bills\BillRepositoryInterface;
use App\Services\Bills\BillService;
use Illuminate\Http\Request;

class BillController extends Controller
{
    protected BillService $billService;
    protected BillRepositoryInterface $billRepository;

    public function __construct(
        BillService $billService,
        BillRepositoryInterface $billRepository
    ) {
        $this->billService = $billService;
        $this->billRepository = $billRepository;
    }

    public function createBill(StoreBillRequest $request)
    {
        $data = $request->only([
            'order_id',
            'discount_amount',
            'delivery_fee',
            'status',
            'bill_code',
        ]);

        $result = $this->billService->createBill($data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function getBillDetail(int $id)
    {
        $result = $this->billService->getBillDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }

        return $this->responseSuccess($result->getData());
    }

    public function updateBill(UpdateBillRequest $request, $id)
    {
        $data = $request->only([
            'sub_total',
            'discount_amount',
            'delivery_fee',
            'final_amount',
            'status',
        ]);

        $result = $this->billService->updateBill($id, $data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

}
