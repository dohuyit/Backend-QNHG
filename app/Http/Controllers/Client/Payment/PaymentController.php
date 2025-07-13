<?php

namespace App\Http\Controllers\Client\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderPaymentRequest\StoreClientPaymentRequest;
use App\Services\Payments\ClientPaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected ClientPaymentService $paymentService;

    public function __construct(
        ClientPaymentService $paymentService,
    ) {
        $this->paymentService = $paymentService;
    }

    public function handlePayment(StoreClientPaymentRequest $request, int $orderId)
    {
        $data = $request->only([
            'payment_method',
            'amount_paid',
            'notes',
            'discount_amount',
            'delivery_fee',
            'user_id'
        ]);

        $result = $this->paymentService->handlePayment($orderId, $data);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(
            $result->getData(),
            $result->getMessage()
        );
    }

      public function vnpayReturn(Request $request)
    {
        $result = $this->paymentService->handleVnpayReturn($request);
        $redirectBase = 'http://localhost:5173/payment-result';

        if ($result->isSuccessCode()) {
            return redirect()->away("{$redirectBase}?status=success&message=" . urlencode($result->getMessage()));
        }

        return redirect()->away("{$redirectBase}?status=fail&message=" . urlencode($result->getMessage()));
    }

    public function momoReturn(Request $request)
    {
        $result = $this->paymentService->handleMomoReturn($request->all());
        $redirectBase = 'http://localhost:5173/payment-result';

        if ($result->isSuccessCode()) {
            return redirect()->away("{$redirectBase}?status=success&message=" . urlencode($result->getMessage()));
        }

        return redirect()->away("{$redirectBase}?status=fail&message=" . urlencode($result->getMessage()));
    }
}
