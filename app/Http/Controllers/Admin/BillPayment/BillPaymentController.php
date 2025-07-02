<?php

namespace App\Http\Controllers\Admin\BillPayment;

use App\Http\Controllers\Controller;
use App\Http\Requests\BillPaymentRequest\CreateBillPaymentRequest;
use App\Http\Requests\BillPaymentRequest\UpdateBillPaymentRequest;
use App\Services\BillPayments\BillPaymentService;

class BillPaymentController extends Controller
{
    protected BillPaymentService $billPaymentService;

    public function __construct(BillPaymentService $billPaymentService)
    {
        $this->billPaymentService = $billPaymentService;
    }

    public function createBillPayment(CreateBillPaymentRequest $request)
    {
        $data = $request->only([
            'bill_id',
            'payment_method',
            'amount_paid',
            'transaction_ref',
            'notes',
            'user_id',
        ]);

        $result = $this->billPaymentService->createPayment($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage(), data: $result->getData());
    }

    public function getPaymentsByBill(int $billId)
    {
        $result = $this->billPaymentService->getPaymentsByBill($billId);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }

        return $this->responseSuccess(data: $result->getData());
    }

    public function updateBillPayment(UpdateBillPaymentRequest $request, int $id)
    {
        $data = $request->only([
            'payment_method',
            'amount_paid',
            'transaction_ref',
            'notes',
            'payment_time',
        ]);

        $result = $this->billPaymentService->updatePayment($id, $data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage(), data: $result->getData());
    }
}
