<?php

namespace App\Http\Controllers\Admin\OrderPayment;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPaymentRequest\StoreOrderPaymentRequest;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\OrderPayments\OrderPaymentService;
use Illuminate\Http\Request;

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
    
    public function createPaymentUrl(Request $request, int $orderId)
    {
        return $this->orderPaymentService->generateVnpayUrl($orderId, $request);
    }
public function vnpayReturn(Request $request)
{
    $result = $this->orderPaymentService->handleVnpayReturn($request);

    if ($result->isSuccessCode()) {
        return response()->json([
            'status' => 'success',
            'message' => $result->getMessage(),
            'data' => $result->getData(),
        ], 200);
    }

    return response()->json([
        'status' => 'fail',
        'message' => $result->getMessage() ?: 'Thanh toán thất bại hoặc chữ ký không hợp lệ.',
    ], 400);
}


}
