<?php

namespace App\Services\Payments;

use App\Common\DataAggregate;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Payment\PaymentRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class PaymentService
{
    protected OrderRepositoryInterface $orderRepository;
    protected PaymentRepositoryInterface $paymentRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        PaymentRepositoryInterface $paymentRepository,
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
            $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);
            if ($bill) {
                if ($bill->status !== 'cancelled') {
                    $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'cancelled']);
                    $result->setMessage('Đơn hàng đã huỷ. Bill cũng được cập nhật trạng thái huỷ, không thể thanh toán.');
                } else {
                    $result->setMessage('Đơn hàng và bill đều đã bị huỷ. Không thể thanh toán.');
                }
            } else {
                $result->setMessage('Đơn hàng đã huỷ. Không thể tạo bill và tiến hành thanh toán.');
            }
            return $result;
        }

        $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);
        if (!$bill) {
            $subTotal = round((float)$order->total_amount, 2);
            $discount = isset($data['discount_amount']) ? round((float)$data['discount_amount'], 2) : 0.0;
            $deliveryFee = isset($data['delivery_fee']) ? round((float)$data['delivery_fee'], 2) : 0.0;
            $finalAmount = round($subTotal + $deliveryFee - $discount, 2);

            $bill = $this->paymentRepository->createBill([
                'bill_code' => strtoupper('B' . now()->format('YmdHis') . rand(10, 99)),
                'order_id' => $order->id,
                'sub_total' => $subTotal,
                'discount_amount' => $discount,
                'delivery_fee' => $deliveryFee,
                'final_amount' => $finalAmount,
                'status' => 'unpaid',
                'user_id' => $data['user_id'] ?? Auth::id(),
            ]);
            $this->orderRepository->updateByConditions(['id' => $order->id], [
                'final_amount' => $finalAmount,
            ]);
        }

        $totalPaid = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
        $finalAmount = round((float)$bill->final_amount, 2);

        if ($bill->status === 'paid' || abs($totalPaid - $finalAmount) <= 1) {
            $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
            $result->setResultSuccess(
                message: 'Bill đã được thanh toán đủ.',
                data: [
                    'bill' => $bill->fresh(),
                    'remaining' => 0.0,
                ]
            );
            return $result;
        }

        $remaining = round($finalAmount - $totalPaid, 2);
        $amountPaid = (!isset($data['amount_paid']) || empty($data['amount_paid']) || $data['amount_paid'] <= 0)
            ? $remaining
            : round((float)$data['amount_paid'], 2);

        if ($amountPaid > $remaining) {
            $result->setMessage('Số tiền thanh toán vượt quá số tiền còn phải trả: ' . number_format($remaining, 2) . ' VND');
            return $result;
        }

        if ($data['payment_method'] === 'vnpay') {
            $amountPaidForVnpay = ($amountPaid > 0) ? $amountPaid : $remaining;

            if ($amountPaidForVnpay < 10000) {
                $payment = $this->paymentRepository->createPayment([
                    'bill_id' => $bill->id,
                    'payment_method' => 'cash',
                    'amount_paid' => $amountPaidForVnpay,
                    'user_id' => $data['user_id'] ?? Auth::id(),
                    'payment_time' => now(),
                    'notes' => 'Thanh toán bằng tiền mặt (auto fallback do số tiền nhỏ).',
                ]);

                $totalPaidAfter = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
                $remainingAfter = round($finalAmount - $totalPaidAfter, 2);

                if ($remainingAfter <= 1) {
                    $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
                    $message = 'Đã thanh toán đủ, bill hoàn tất.';
                    $remainingAfter = 0.0;
                } else {
                    $message = 'Thanh toán một phần. Còn thiếu: ' . number_format($remainingAfter, 2) . ' VND';
                }

                $result->setResultSuccess(
                    message: 'Số tiền quá nhỏ nên đã được thanh toán bằng tiền mặt.',
                    data: [
                        'bill' => $bill->fresh(),
                        'payment' => $payment,
                        'remaining' => $remainingAfter,
                    ]
                );
                return $result;
            }

            $amountPaidForVnpay = round($amountPaidForVnpay, 2);
            $paymentResult = $this->generateVnpayUrl($order->id, $amountPaidForVnpay);

            $paymentUrl = $paymentResult->getData()['payment_url'] ?? '';

            $result->setResultSuccess(
                message: 'Vui lòng truy cập URL để thanh toán VNPay',
                data: [
                    'payment_url' => $paymentUrl,
                    'bill' => $bill,
                    'remaining' => $remaining,
                ]
            );
            return $result;
        }

        if ($data['payment_method'] === 'momo') {
            $amountPaidForMomo = ($amountPaid > 0) ? $amountPaid : $remaining;

            if ($amountPaidForMomo < 10000) {
                $payment = $this->paymentRepository->createPayment([
                    'bill_id' => $bill->id,
                    'payment_method' => 'cash',
                    'amount_paid' => $amountPaidForMomo,
                    'user_id' => $data['user_id'] ?? Auth::id(),
                    'payment_time' => now(),
                    'notes' => 'Thanh toán bằng tiền mặt (auto fallback do số tiền nhỏ).',
                ]);

                $totalPaidAfter = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
                $remainingAfter = round($finalAmount - $totalPaidAfter, 2);

                if ($remainingAfter <= 1) {
                    $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
                    $message = 'Đã thanh toán đủ, bill hoàn tất.';
                    $remainingAfter = 0.0;
                } else {
                    $message = 'Thanh toán một phần. Còn thiếu: ' . number_format($remainingAfter, 2) . ' VND';
                }

                $result->setResultSuccess(
                    message: 'Số tiền quá nhỏ nên đã được thanh toán bằng tiền mặt.',
                    data: [
                        'bill' => $bill->fresh(),
                        'payment' => $payment,
                        'remaining' => $remainingAfter,
                    ]
                );
                return $result;
            }

            $amountPaidForMomo = round($amountPaidForMomo, 2);
            $paymentResult = $this->generateMomoUrl($order->id, $amountPaidForMomo);

            $paymentUrl = $paymentResult->getData()['payment_url'] ?? '';

            $result->setResultSuccess(
                message: 'Vui lòng truy cập URL để thanh toán Momo',
                data: [
                    'payment_url' => $paymentUrl,
                    'bill' => $bill,
                    'remaining' => $remaining,
                ]
            );
            return $result;
        }

        $payment = $this->paymentRepository->createPayment([
            'bill_id' => $bill->id,
            'payment_method' => $data['payment_method'],
            'amount_paid' => $amountPaid,
            'user_id' => $data['user_id'] ?? Auth::id(),
            'payment_time' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $totalPaidAfter = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAfter = round($finalAmount - $totalPaidAfter, 2);

        if ($remainingAfter <= 1) {
            $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
            $message = 'Đã thanh toán đủ, bill hoàn tất.';
            $remainingAfter = 0.0;
        } else {
            $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'unpaid']);
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

    public function generateVnpayUrl(int $orderId, float $amountPaid): DataAggregate
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

        $totalPaid = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAmount = round((float)$bill->final_amount - $totalPaid, 2);

        if ($remainingAmount <= 1) {
            $result->setMessage("Bill đã được thanh toán đủ.");
            return $result;
        }
        if ($amountPaid <= 0 || $amountPaid > $remainingAmount) {
            $result->setMessage("Số tiền thanh toán không hợp lệ.");
            return $result;
        }

        date_default_timezone_set('Asia/Ho_Chi_Minh');

        $vnp_TmnCode = config('services.vnpay.tmn_code');
        $vnp_HashSecret = config('services.vnpay.hash_secret');
        $vnp_Url = config('services.vnpay.url');
        $vnp_ReturnUrl = config('services.vnpay.return_url');

        $vnp_TxnRef = $order->order_code . '-' . time();

        $vnp_OrderInfo = "Thanh toán phần còn thiếu đơn hàng #" . $order->order_code;
        $vnp_OrderType = "billpayment";
        $vnp_Amount = intval(round($amountPaid, 2) * 100);
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
            message: "Tạo URL VNPay thành công.",
            data: [
                'payment_url' => $vnp_Url,
                'bill' => $bill,
                'remaining' => $remainingAmount
            ]
        );
        return $result;
    }

    public function handleVnpayReturn($request): DataAggregate
    {
        $result = new DataAggregate();
        $inputData = $request->all();
        $vnp_HashSecret = config('services.vnpay.hash_secret');
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
        $orderCode = implode('-', array_slice(explode('-', $inputData['vnp_TxnRef']), 0, 2));

        $order = $this->orderRepository->getByConditions(['order_code' => $orderCode]);

        if (!$order) {
            $result->setMessage('Đơn hàng không tồn tại.');
            return $result;
        }

        $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);
        if (!$bill) {
            $bill = $this->paymentRepository->createBill([
                'bill_code' => strtoupper('B' . now()->format('YmdHis') . rand(10, 99)),
                'order_id' => $order->id,
                'sub_total' => round((float)$order->total_amount, 2),
                'discount_amount' => 0,
                'delivery_fee' => 0,
                'final_amount' => round((float)$order->total_amount, 2),
                'status' => 'unpaid',
                'user_id' => $order->user_id ?? Auth::id() ?? 1,
            ]);
        }

        $transactionRef = $inputData['vnp_TransactionNo'] ?? null;

        $existingPayment = $this->paymentRepository->getPaymentByConditions([
            'transaction_ref' => $transactionRef
        ]);

        if ($existingPayment) {
            $totalPaid = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
            $remaining = round((float)$bill->final_amount - $totalPaid, 2);

            if ($remaining <= 1) {
                $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
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

        $amountPaid = round((float)$inputData['vnp_Amount'] / 100, 2);
        $payment = $this->paymentRepository->createPayment([
            'bill_id' => $bill->id,
            'payment_method' => 'vnpay',
            'amount_paid' => $amountPaid,
            'payment_time' => now(),
            'transaction_ref' => $transactionRef,
            'user_id' => $order->user_id ?? Auth::id() ?? 1,
            'notes' => 'Thanh toán VNPay thành công. Mã giao dịch: ' . $transactionRef,
        ]);

        $totalPaidAfter = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAfter = round((float)$bill->final_amount - $totalPaidAfter, 2);

        if (abs($remainingAfter) < 1) {
            $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
            $message = 'Đã thanh toán đủ, bill hoàn tất.';
            $remainingAfter = 0.0;
        } else {
            $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'unpaid']);
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

    public function generateMomoUrl(int $orderId, float $amountPaid): DataAggregate
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

        $totalPaid = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
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

        return $this->processMomoPayment($inputData['orderId'], $inputData['amount'], $inputData['transId']);
    }

    private function processMomoPayment($orderId, $amountPaid, $transactionRef): DataAggregate
    {
        $result = new DataAggregate();
        $orderCode = implode('-', array_slice(explode('-', $orderId), 0, 2));

        $order = $this->orderRepository->getByConditions(['order_code' => $orderCode]);

        if (!$order) {
            $result->setMessage("Đơn hàng không tồn tại.");
            return $result;
        }

        $bill = $this->paymentRepository->getBillByConditions(['order_id' => $order->id]);
        if (!$bill) {
            $bill = $this->paymentRepository->createBill([
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

        $existingPayment = $this->paymentRepository->getPaymentByConditions([
            'transaction_ref' => $transactionRef
        ]);

        if ($existingPayment) {
            $totalPaid = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
            $remaining = round((float)$bill->final_amount - $totalPaid, 2);

            if ($remaining <= 1) {
                $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
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
        $payment = $this->paymentRepository->createPayment([
            'bill_id' => $bill->id,
            'payment_method' => 'momo',
            'amount_paid' => $amountPaid,
            'payment_time' => now(),
            'transaction_ref' => $transactionRef,
            'user_id' => $order->user_id ?? Auth::id(),
            'notes' => 'Thanh toán MoMo thành công. Mã giao dịch: ' . $transactionRef,
        ]);

        $totalPaidAfter = round((float)$this->paymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAfter = round((float)$bill->final_amount - $totalPaidAfter, 2);

        if (abs($remainingAfter) < 1) {
            $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'paid']);
            $message = 'Đã thanh toán đủ, bill hoàn tất.';
            $remainingAfter = 0.0;
        } else {
            $this->paymentRepository->updateBillByConditions(['id' => $bill->id], ['status' => 'unpaid']);
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