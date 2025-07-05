<?php

namespace App\Http\Controllers\Admin\OrderPayment;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPaymentRequest\StoreOrderPaymentRequest;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\OrderPayments\OrderPaymentService;
use App\Services\PaymentGateways\MomoService;
use App\Services\PaymentGateways\VnpayService;
use Illuminate\Http\Request;

class OrderPaymentController  extends Controller
{
    protected OrderPaymentService $orderPaymentService;
    protected OrderRepositoryInterface $orderRepository;
    protected VnpayService $vnpayService;
    protected MomoService $momoService;

    public function __construct(OrderPaymentService $orderPaymentService, OrderRepositoryInterface $orderRepository, VnpayService $vnpayService, MomoService $momoService)
    {
        $this->orderPaymentService = $orderPaymentService;
        $this->orderRepository = $orderRepository;
        $this->vnpayService = $vnpayService;
        $this->momoService = $momoService;
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
        $amountPaid = (float)$request->input('amount_paid', 0);

        return $this->vnpayService->generateVnpayUrl($orderId, $amountPaid);
    }

    public function vnpayReturn(Request $request)
    {
        $result = $this->vnpayService->handleVnpayReturn($request);

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
    public function momoReturn(Request $request)
    {
        $result = $this->momoService->handleMomoReturn($request->all());

        if ($result->isSuccessCode()) {
            return response()->json([
                'status' => 'success',
                'message' => $result->getMessage(),
                'data' => $result->getData(),
            ], 200);
        }

        return response()->json([
            'status' => 'fail',
            'message' => $result->getMessage() ?: 'Thanh toán Momo thất bại hoặc chữ ký không hợp lệ.',
        ], 400);
    }

    public function getListBills()
    {
        $params = request()->only([
            'page',
            'limit',
            'bill_code',
            'order_id',
            'status',
            'user_id',
            'issued_from',
            'issued_to'
        ]);

        $result = $this->orderPaymentService->getListBill($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function countByStatus()
    {
        $result = $this->orderPaymentService->countByStatus();

        return $this->responseSuccess($result);
    }
}
