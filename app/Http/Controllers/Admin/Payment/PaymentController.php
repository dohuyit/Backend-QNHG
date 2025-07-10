<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPaymentRequest\StoreOrderPaymentRequest;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\Payments\PaymentService;

use Illuminate\Http\Request;

class PaymentController  extends Controller
{
    protected PaymentService $paymentService;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(PaymentService $paymentService, OrderRepositoryInterface $orderRepository)
    {
        $this->paymentService = $paymentService;
        $this->orderRepository = $orderRepository;
    }

    public function payment(StoreOrderPaymentRequest $request, int $id)
    {
        $order = $this->orderRepository->getByConditions(['id' => $id]);
        if (!$order) {
            return $this->responseFail(message: 'Đơn hàng không tồn tại', statusCode: 404);
        }
        $data = $request->only([
            'payment_method',
            'amount_paid',
            'notes',
            'discount_amount',
            'delivery_fee',
            'user_id'
        ]);

        $result = $this->paymentService->handlePayment($id, $data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(
            $result->getData(),
            $result->getMessage()
        );
    }

    public function createPaymentUrl(Request $request, int $orderId)
    {
        $amountPaid = (float)$request->input('amount_paid', 0);

        return $this->paymentService->generateVnpayUrl($orderId, $amountPaid);
    }

    public function vnpayReturn(Request $request)
    {
        $result = $this->paymentService->handleVnpayReturn($request);

        if ($result->isSuccessCode()) {
            return $this->responseSuccess(
                $result->getData(),
                $result->getMessage()
            );
        }

        return $this->responseFail(
            message: $result->getMessage() ?: 'Thanh toán VNPAY thất bại hoặc chữ ký không hợp lệ.',
            statusCode: 400
        );
    }

    public function momoReturn(Request $request)
    {
        $result = $this->paymentService->handleMomoReturn($request->all());

        if ($result->isSuccessCode()) {
            return $this->responseSuccess(
                $result->getData(),
                $result->getMessage()
            );
        }

        return $this->responseFail(
            message: $result->getMessage() ?: 'Thanh toán Momo thất bại hoặc chữ ký không hợp lệ.',
            statusCode: 400
        );
    }

    public function getBillDetailForOrder(string $id)
    {
        $result = $this->paymentService->getBillDetail($id);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }
        $data = $result->getData();
        return $this->responseSuccess($data);
    }
}
