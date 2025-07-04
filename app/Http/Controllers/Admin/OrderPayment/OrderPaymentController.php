<?php

namespace App\Http\Controllers\Admin\OrderPayment;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPaymentRequest\StoreOrderPaymentRequest;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\OrderPayments\OrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderPaymentController  extends Controller
{
    protected OrderPaymentService $orderPaymentService;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(OrderPaymentService $orderPaymentService, OrderRepositoryInterface $orderRepository)
    {
        $this->orderPaymentService = $orderPaymentService;
        $this->orderRepository = $orderRepository;

    }

    public function pay(StoreOrderPaymentRequest $request, int $id)
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

        $result = $this->orderPaymentService->handlePayment($id, $data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(
            $result->getData(),
            $result->getMessage()
        );
    }
      // FE gọi API này để lấy URL thanh toán
    public function createPaymentUrl(Request $request, int $orderId)
    {
        return $this->orderPaymentService->generateVnpayUrl($orderId, $request);
    }

    // VNPay gọi về URL này khi thanh toán xong
   public function vnpayReturn(Request $request)
{
    return $this->orderPaymentService->handleVnpayReturn($request);
}


    // API giả lập callback để test bằng Postman
    public function fakeVnpayCallback(Request $request, int $orderId)
    {
        return $this->orderPaymentService->fakeVnpayCallback($orderId, $request);
    }
}
