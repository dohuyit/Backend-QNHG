<?php

namespace App\Services\PaymentGateways;

use App\Common\DataAggregate;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Bills\BillRepositoryInterface;
use App\Repositories\BillPayments\BillPaymentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MomoService
{
    protected OrderRepositoryInterface $orderRepository;
    protected BillRepositoryInterface $billRepository;
    protected BillPaymentRepositoryInterface $billPaymentRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        BillRepositoryInterface $billRepository,
        BillPaymentRepositoryInterface $billPaymentRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->billRepository = $billRepository;
        $this->billPaymentRepository = $billPaymentRepository;
    }

    public function generateMomoUrl(int $orderId, float $amountPaid): DataAggregate
    {
        $result = new DataAggregate();
        $order = $this->orderRepository->getByConditions(['id' => $orderId]);

        if (!$order) {
            $result->setMessage("Đơn hàng không tồn tại.");
            return $result;
        }

        $bill = $this->billRepository->getByConditions(['order_id' => $order->id]);
        if (!$bill) {
            $result->setMessage("Bill không tồn tại cho đơn hàng #{$order->order_code}.");
            return $result;
        }

        $totalPaid = round((float)$this->billPaymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAmount = round((float)$bill->final_amount - $totalPaid, 2);

        if ($remainingAmount <= 1) {
            $result->setMessage("Bill đã được thanh toán đủ.");
            return $result;
        }

        if ($amountPaid <= 0 || $amountPaid > $remainingAmount) {
            $result->setMessage("Số tiền thanh toán không hợp lệ.");
            return $result;
        }

        $endpoint = config('services.momo.endpoint');
        $partnerCode = config('services.momo.partner_code');
        $accessKey = config('services.momo.access_key');
        $secretKey = config('services.momo.secret_key');
        $redirectUrl = config('services.momo.return_url');
        $ipnUrl = config('services.momo.notify_url');
        $requestType = 'payWithMethod';
        $orderInfo = "Thanh toán đơn hàng #" . $order->order_code;

        $requestId = uniqid();
        $momoOrderId = $order->order_code . '-' . time();
        $extraData = '';

        $amountInt = (int)$amountPaid;
        $rawHash = "accessKey={$accessKey}&amount={$amountInt}&extraData={$extraData}&ipnUrl={$ipnUrl}&orderId={$momoOrderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}&requestType={$requestType}";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => (string)$amountInt,
            'orderId' => $momoOrderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
            'lang' => 'vi'
        ];

        $response = $this->callApi($endpoint, $payload);
        Log::info('MoMo API Response: ' . json_encode($response));

        if (isset($response['payUrl']) && !empty($response['payUrl'])) {
            $result->setResultSuccess(
                message: "Vui lòng truy cập URL để thanh toán Momo",
                data: [
                    'payment_url' => $response['payUrl'],
                    'bill' => $bill,
                    'remaining' => $remainingAmount
                ]
            );
        } else {
            $result->setMessage("Tạo URL MoMo thất bại: " . ($response['message'] ?? 'Không rõ lỗi'));
        }

        return $result;
    }
    public function handleMomoReturn($inputData): DataAggregate
    {
        $result = new DataAggregate();
        $secretKey = config('services.momo.secret_key');
        $accessKey = config('services.momo.access_key'); 

        if (isset($inputData['signature'])) {
            $rawHash = "accessKey={$accessKey}&amount={$inputData['amount']}&extraData={$inputData['extraData']}&message={$inputData['message']}&orderId={$inputData['orderId']}&orderInfo={$inputData['orderInfo']}&orderType={$inputData['orderType']}&partnerCode={$inputData['partnerCode']}&payType={$inputData['payType']}&requestId={$inputData['requestId']}&responseTime={$inputData['responseTime']}&resultCode={$inputData['resultCode']}&transId={$inputData['transId']}";
            $calculatedSignature = hash_hmac('sha256', $rawHash, $secretKey);

            if ($calculatedSignature !== $inputData['signature']) {
                $result->setMessage("Chữ ký không hợp lệ.");
                return $result;
            }
        }

        if ($inputData['resultCode'] != 0) {
            $result->setMessage("Thanh toán thất bại: {$inputData['message']}");
            return $result;
        }

        return $this->processPayment($inputData['orderId'], $inputData['amount'], $inputData['transId']);
    }

    private function processPayment($orderId, $amountPaid, $transactionRef): DataAggregate
    {
        $result = new DataAggregate();
        $orderCode = implode('-', array_slice(explode('-', $orderId), 0, 2));

        $order = $this->orderRepository->getByConditions(['order_code' => $orderCode]);

        if (!$order) {
            $result->setMessage("Đơn hàng không tồn tại.");
            return $result;
        }

        $bill = $this->billRepository->getByConditions(['order_id' => $order->id]);
        if (!$bill) {
            $bill = $this->billRepository->createDataAndReturn([
                'bill_code' => strtoupper('B' . now()->format('YmdHis') . rand(10, 99)),
                'order_id' => $order->id,
                'sub_total' => round((float)$order->total_amount, 2),
                'discount_amount' => 0,
                'delivery_fee' => 0,
                'final_amount' => round((float)$order->total_amount, 2),
                'status' => 'unpaid',
                'user_id' => $order->user_id ?? Auth::id(),
            ]);
        }

        $existingPayment = $this->billPaymentRepository->getByConditions([
            'transaction_ref' => $transactionRef
        ]);

        if ($existingPayment) {
            $totalPaid = round((float)$this->billPaymentRepository->sumPaymentsForBill($bill->id), 2);
            $remaining = round((float)$bill->final_amount - $totalPaid, 2);

            if ($remaining <= 1) {
                $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'paid']);
                $remaining = 0.0;
            }

            $result->setResultSuccess(
                message: 'Giao dịch đã tồn tại hoặc đã được xử lý.',
                data: [
                    'bill' => $bill->fresh(),
                    'payment' => $existingPayment,
                    'remaining' => $remaining,
                ]
            );
            return $result;
        }

        $amountPaid = round((float)$amountPaid, 2);
        $payment = $this->billPaymentRepository->createData([
            'bill_id' => $bill->id,
            'payment_method' => 'momo',
            'amount_paid' => $amountPaid,
            'payment_time' => now(),
            'transaction_ref' => $transactionRef,
            'user_id' => $order->user_id ?? Auth::id(),
            'notes' => 'Thanh toán MoMo thành công. Mã giao dịch: ' . $transactionRef,
        ]);

        $totalPaidAfter = round((float)$this->billPaymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAfter = round((float)$bill->final_amount - $totalPaidAfter, 2);

        if (abs($remainingAfter) < 1) {
            $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'paid']);
            $this->orderRepository->updateByConditions(['id' => $order->id], ['status' => 'completed']);
            $message = 'Đã thanh toán đủ, bill hoàn tất.';
            $remainingAfter = 0.0;
        } else {
            $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'unpaid']);
            $message = 'Thanh toán một phần. Còn thiếu: ' . number_format($remainingAfter, 2) . ' VND';
        }

        $result->setResultSuccess(
            message: $message,
            data: [
                'bill' => $bill->fresh(),
                'payment' => $payment,
                'remaining' => $remainingAfter,
            ]
        );

        return $result;
    }

    private function callApi($endpoint, $data)
    {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result, true);
    }
}
