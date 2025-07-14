<?php

namespace App\Services\Payments;

use App\Common\DataAggregate;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Payment\PaymentRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientPaymentService
{
    protected OrderRepositoryInterface $orderRepository;
    protected PaymentRepositoryInterface $paymentRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PaymentRepositoryInterface $paymentRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentRepository = $paymentRepository;
    }

    public function handlePayment(int $orderId, array $data): DataAggregate
    {
        $result = new DataAggregate();

        $order = $this->orderRepository->getByConditions(['id' => $orderId]);
        if (!$order) {
            $result->setMessage('Đơn hàng không tồn tại.');
            return $result;
        }
        if ($order->status === 'cancelled') {
            $result->setMessage('Đơn hàng đã huỷ.');
            return $result;
        }

        $userId = $data['user_id'] ?? Auth::id() ?? $order->user_id;
        $paymentMethod = $data['payment_method'] ?? 'cash';

        $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);

        if ($bill && $bill->status === 'paid') {
            $result->setMessage('Đơn hàng đã được thanh toán.');
            return $result;
        }

        $subTotal = round((float)$order->total_amount, 2);
        $deliveryFee = $subTotal >= 500000 ? 0.0 : 40000; // free ship nếu >= 500k
        $discount = 0.0;
        $finalAmount = round($subTotal + $deliveryFee - $discount, 2);

        if ($finalAmount <= 0) {
            $result->setMessage('Số tiền thanh toán không hợp lệ.');
            return $result;
        }

        if ($bill && $bill->status === 'unpaid') {
            $payment = $this->paymentRepository->getPaymentByConditions([
                'bill_id' => $bill->id
            ]);

            if ($payment && $payment->payment_method === 'cash' && $paymentMethod !== 'cash') {
                $result->setMessage('Đơn hàng đang chờ thanh toán bằng tiền mặt. Không thể đổi sang thanh toán online.');
                return $result;
            }

            if ($paymentMethod === 'cash') {
                $result->setResultSuccess(
                    message: 'Đơn hàng đã được tạo trước đó. Vui lòng thanh toán ' . number_format($bill->final_amount) . ' VND khi nhận hàng.',
                    data: ['bill' => $bill]
                );
                return $result;
            }

            if ($paymentMethod === 'vnpay') {
                return $this->generateVnpayUrl($order->id);
            }
            if ($paymentMethod === 'momo') {
                return $this->generateMomoUrl($order->id);
            }
        }

        $bill = $this->paymentRepository->createBill([
            'bill_code'       => strtoupper('B' . now()->format('YmdHis') . rand(10, 99)),
            'order_id'        => $order->id,
            'sub_total'       => $subTotal,
            'delivery_fee'    => $deliveryFee,
            'discount_amount' => $discount,
            'final_amount'    => $finalAmount,
            'status'          => 'unpaid',
            'user_id'         => $userId,
        ]);

        $this->orderRepository->updateByConditions(['id' => $order->id], [
            'final_amount' => $finalAmount
        ]);

        if ($paymentMethod === 'cash') {
            $this->paymentRepository->createPayment([
                'bill_id'      => $bill->id,
                'method'       => 'cash',
                'amount_paid'  => $finalAmount,
                'status'       => 'unpaid',
                'user_id'      => $userId,
                'note'         => $data['note'] ?? null,
            ]);

            $result->setResultSuccess(
                message: 'Đặt hàng thành công. Vui lòng thanh toán ' . number_format($finalAmount) . ' VND khi nhận hàng.',
                data: ['bill' => $bill]
            );
            return $result;
        }

        if ($paymentMethod === 'vnpay') {
            return $this->generateVnpayUrl($order->id);
        }

        if ($paymentMethod === 'momo') {
            return $this->generateMomoUrl($order->id);
        }

        $result->setMessage('Phương thức thanh toán không hợp lệ.');
        return $result;
    }

    public function generateVnpayUrl(int $orderId): DataAggregate
    {
        $result = new DataAggregate();
        $order = $this->orderRepository->getByConditions(['id' => $orderId]);
        if (!$order) {
            $result->setMessage("Đơn hàng không tồn tại.");
            return $result;
        }

        $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);
        if (!$bill || $bill->status !== 'unpaid') {
            $result->setMessage("Hóa đơn không tồn tại hoặc đã được thanh toán.");
            return $result;
        }

        $finalAmount = round((float)$bill->final_amount, 2);
        if ($finalAmount <= 0) {
            $result->setMessage("Số tiền thanh toán không hợp lệ.");
            return $result;
        }

        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $vnp_TmnCode = config('services.vnpay_client.tmn_code');
        $vnp_HashSecret = config('services.vnpay_client.hash_secret');
        $vnp_Url = config('services.vnpay_client.url');
        $vnp_ReturnUrl = config('services.vnpay_client.return_url');

        $vnp_TxnRef = $order->order_code . '-' . time();
        $vnp_OrderInfo = "Thanh toán đơn hàng #" . $order->order_code;
        $vnp_OrderType = "billpayment";
        $vnp_Amount = intval(round($finalAmount, 2) * 100);
        $vnp_IpAddr = request()->ip();
        $vnp_CreateDate = date('YmdHis');
        $vnp_ExpireDate = date('YmdHis', strtotime('+15 minutes'));
        $vnp_Locale = "vn";

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $vnp_IpAddr,
            "vnp_Locale" => $vnp_Locale,
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_OrderType" => $vnp_OrderType,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
            "vnp_ExpireDate" => $vnp_ExpireDate
        ];

        ksort($inputData);

        $hashdata = '';
        $query = '';
        $i = 0;
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashdata .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashdata .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
            $query .= urlencode($key) . '=' . urlencode($value) . '&';
        }

        $vnp_SecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
        $vnp_Url .= '?' . $query . 'vnp_SecureHash=' . $vnp_SecureHash;

        $result->setResultSuccess(
            message: "Vui lòng truy cập URL để thanh toán VNPay.",
            data: [
                'payment_url' => $vnp_Url,
                'bill' => $bill,
                'final_amount' => $finalAmount
            ]
        );
        return $result;
    }

    public function handleVnpayReturn($request): DataAggregate
    {
        $result = new DataAggregate();
        $inputData = $request->all();
        $vnp_HashSecret = config('services.vnpay_client.hash_secret');
        $vnp_SecureHash = $inputData['vnp_SecureHash'] ?? '';

        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
        ksort($inputData);

        $hashData = '';
        foreach ($inputData as $key => $value) {
            $hashData .= ($hashData ? '&' : '') . urlencode($key) . '=' . urlencode($value);
        }
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash !== $vnp_SecureHash) {
            $result->setMessage('Chữ ký không hợp lệ.');
            return $result;
        }

        if ($inputData['vnp_ResponseCode'] !== '00') {
            $result->setMessage('Thanh toán thất bại.');
            return $result;
        }

        $orderCodeWithTime = $inputData['vnp_TxnRef'];
        $orderCode = substr($orderCodeWithTime, 0, strrpos($orderCodeWithTime, '-'));

        $order = $this->orderRepository->getByConditions(['order_code' => $orderCode]);
        if (!$order) {
            $result->setMessage('Đơn hàng không tồn tại.');
            return $result;
        }

        $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);
        if (!$bill) {
            $bill = $this->paymentRepository->createBill([
                'bill_code'       => strtoupper('B' . now()->format('YmdHis') . rand(10, 99)),
                'order_id'        => $order->id,
                'sub_total'       => $order->total_amount,
                'discount_amount' => 0,
                'delivery_fee'    => 0,
                'final_amount'    => $order->total_amount,
                'status'          => 'unpaid',
                'user_id'         => $order->user_id ?? Auth::id() ?? 1,
            ]);
        }

        if ($bill->status === 'paid') {
            $result->setResultSuccess(
                message: 'Đơn hàng đã được thanh toán.',
                data: ['bill' => $bill]
            );
            return $result;
        }

        $transactionRef = $inputData['vnp_TransactionNo'] ?? null;

        $payment = $this->paymentRepository->createPayment([
            'bill_id'         => $bill->id,
            'payment_method'  => 'vnpay',
            'amount_paid'     => $bill->final_amount,
            'payment_time'    => now(),
            'transaction_ref' => $transactionRef,
            'user_id'         => $order->user_id ?? Auth::id() ?? 1,
            'notes'           => 'Thanh toán VNPay thành công. Mã giao dịch: ' . $transactionRef,
        ]);


        $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);

        $result->setResultSuccess(
            message: 'Thanh toán thành công. Đơn hàng đã được xử lý.',
            data: [
                'bill'    => $bill->fresh(),
                'payment' => $payment,
            ]
        );

        return $result;
    }

    public function generateMomoUrl(int $orderId): DataAggregate
    {
        $result = new DataAggregate();
        $order = $this->orderRepository->getByConditions(['id' => $orderId]);

        if (!$order) {
            $result->setMessage("Đơn hàng không tồn tại.");
            return $result;
        }

        $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);
        if (!$bill) {
            $result->setMessage("Bill không tồn tại cho đơn hàng #{$order->order_code}.");
            return $result;
        }

        if ($bill->status === 'paid') {
            $result->setMessage("Bill đã được thanh toán đủ.");
            return $result;
        }

        $endpoint = config('services.momo_client.endpoint');
        $partnerCode = config('services.momo_client.partner_code');
        $accessKey = config('services.momo_client.access_key');
        $secretKey = config('services.momo_client.secret_key');
        $redirectUrl = config('services.momo_client.return_url');
        $ipnUrl = config('services.momo_client.notify_url');
        $requestType = 'payWithMethod';
        $orderInfo = "Thanh toán đơn hàng #" . $order->order_code;

        $requestId = uniqid();
        $momoOrderId = $order->order_code . '-' . time();
        $extraData = '';

        $amountInt = (int)$bill->final_amount;
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

        if (isset($response['payUrl']) && !empty($response['payUrl'])) {
            $result->setResultSuccess(
                message: "Vui lòng truy cập URL để thanh toán Momo",
                data: [
                    'payment_url' => $response['payUrl'],
                    'bill' => $bill
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
        $secretKey = config('services.momo_client.secret_key');
        $accessKey = config('services.momo_client.access_key');

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

        return $this->processMomoPayment($inputData['orderId'], $inputData['transId']);
    }

    private function processMomoPayment($orderId, $transactionRef): DataAggregate
    {
        $result = new DataAggregate();
        $orderCode = substr($orderId, 0, strrpos($orderId, '-'));

        $order = $this->orderRepository->getByConditions(['order_code' => $orderCode]);

        if (!$order) {
            $result->setMessage("Đơn hàng không tồn tại.");
            return $result;
        }

        $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);

        if ($bill->status === 'paid') {
            $result->setResultSuccess(
                message: 'Đơn hàng đã được thanh toán.',
                data: ['bill' => $bill]
            );
            return $result;
        }

        $payment = $this->paymentRepository->createPayment([
            'bill_id'         => $bill->id,
            'payment_method'  => 'momo',
            'amount_paid'     => $bill->final_amount,
            'payment_time'    => now(),
            'transaction_ref' => $transactionRef,
            'user_id'         => $order->user_id ?? Auth::id() ?? 1,
            'notes'           => 'Thanh toán MoMo thành công. Mã giao dịch: ' . $transactionRef,
        ]);

        $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
        $this->orderRepository->updateByConditions(['id' => $order->id], ['status' => 'completed']);

        $result->setResultSuccess(
            message: 'Thanh toán thành công. Đơn hàng đã được xử lý.',
            data: [
                'bill'    => $bill->fresh(),
                'payment' => $payment,
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
