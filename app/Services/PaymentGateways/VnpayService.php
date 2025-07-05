<?php

namespace App\Services\PaymentGateways;

use App\Common\DataAggregate;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Bills\BillRepositoryInterface;
use App\Repositories\BillPayments\BillPaymentRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class VnpayService
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

    public function generateVnpayUrl(int $orderId, float $amountPaid): DataAggregate
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
                'user_id' => $order->user_id ?? Auth::id() ?? 1,
            ]);
        }

        $transactionRef = $inputData['vnp_TransactionNo'] ?? null;

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

        $amountPaid = round((float)$inputData['vnp_Amount'] / 100, 2);
        $payment = $this->billPaymentRepository->createData([
            'bill_id' => $bill->id,
            'payment_method' => 'vnpay',
            'amount_paid' => $amountPaid,
            'payment_time' => now(),
            'transaction_ref' => $transactionRef,
            'user_id' => $order->user_id ?? Auth::id() ?? 1,
            'notes' => 'Thanh toán VNPay thành công. Mã giao dịch: ' . $transactionRef,
        ]);

        $totalPaidAfter = round((float)$this->billPaymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAfter = round((float)$bill->final_amount - $totalPaidAfter, 2);

        if (abs($remainingAfter) < 1) {
            $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'paid']);
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
}
