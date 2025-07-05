<?php

namespace App\Services\OrderPayments;

use App\Common\DataAggregate;
use App\Repositories\BillPayments\BillPaymentRepositoryInterface;
use App\Repositories\Bills\BillRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderPaymentService
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

    public function handlePayment(int $orderId, array $data): DataAggregate
    {
        $result = new DataAggregate();

        $order = $this->orderRepository->getByConditions(['id' => $orderId]);
        if (!$order) {
            $result->setMessage('Đơn hàng không tồn tại.');
            return $result;
        }

        if ($order->status === 'cancelled') {
            $bill = $this->billRepository->getByConditions(['order_id' => $order->id]);
            if ($bill) {
                if ($bill->status !== 'cancelled') {
                    $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'cancelled']);
                    $result->setMessage('Đơn hàng đã huỷ. Bill cũng được cập nhật trạng thái huỷ, không thể thanh toán.');
                } else {
                    $result->setMessage('Đơn hàng và bill đều đã bị huỷ. Không thể thanh toán.');
                }
            } else {
                $result->setMessage('Đơn hàng đã huỷ. Không thể tạo bill và tiến hành thanh toán.');
            }
            return $result;
        }

        $bill = $this->billRepository->getByConditions(['order_id' => $order->id]);
        if (!$bill) {
            $subTotal = round((float)$order->total_amount, 2);
            $discount = isset($data['discount_amount']) ? round((float)$data['discount_amount'], 2) : 0.0;
            $deliveryFee = isset($data['delivery_fee']) ? round((float)$data['delivery_fee'], 2) : 0.0;
            $finalAmount = round($subTotal + $deliveryFee - $discount, 2);

            $bill = $this->billRepository->createDataAndReturn([
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

        $totalPaid = round((float)$this->billPaymentRepository->sumPaymentsForBill($bill->id), 2);
        $finalAmount = round((float)$bill->final_amount, 2);


        if ($bill->status === 'paid' || abs($totalPaid - $finalAmount) <= 1) {
            $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'paid']);
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
        $amountPaid = round((float)$data['amount_paid'], 2);

        if ($amountPaid > $remaining) {
            $result->setMessage('Số tiền thanh toán vượt quá số tiền còn phải trả: ' . number_format($remaining, 2) . ' VND');
            return $result;
        }

        if ($data['payment_method'] === 'vnpay') {
            $amountPaidForVnpay = ($amountPaid > 0) ? $amountPaid : $remaining;

            if ($amountPaidForVnpay < 1000) {
                // Auto switch sang cash
                $payment = $this->billPaymentRepository->createData([
                    'bill_id' => $bill->id,
                    'payment_method' => 'cash',
                    'amount_paid' => $amountPaidForVnpay,
                    'user_id' => $data['user_id'] ?? Auth::id(),
                    'payment_time' => now(),
                    'notes' => 'Thanh toán bằng tiền mặt (auto fallback do số tiền nhỏ).',
                ]);

                $totalPaidAfter = round((float)$this->billPaymentRepository->sumPaymentsForBill($bill->id), 2);
                $remainingAfter = round($finalAmount - $totalPaidAfter, 2);

                if ($remainingAfter <= 1) {
                    $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'paid']);
                    $message = 'Đã thanh toán đủ, bill hoàn tất.';
                    $remainingAfter = 0.0;
                } else {
                    $message = 'Thanh toán một phần. Còn thiếu: ' . number_format($remainingAfter, 2) . ' VND';
                }

                $result->setResultSuccess(
                    message: 'Số tiền còn lại quá nhỏ nên đã được thanh toán bằng tiền mặt.',
                    data: [
                        'bill' => $bill->fresh(),
                        'payment' => $payment,
                        'remaining' => $remainingAfter,
                    ]
                );
                return $result;
            }


            $amountPaidForVnpay = round($amountPaidForVnpay, 2);
            $paymentUrl = $this->generateVnpayUrl($order->id, $amountPaidForVnpay);

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

        $payment = $this->billPaymentRepository->createData([
            'bill_id' => $bill->id,
            'payment_method' => $data['payment_method'],
            'amount_paid' => $amountPaid,
            'user_id' => $data['user_id'] ?? Auth::id(),
            'payment_time' => now(),
            'notes' => $data['notes'] ?? null,
        ]);

        $totalPaidAfter = round((float)$this->billPaymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAfter = round($finalAmount - $totalPaidAfter, 2);

        if ($remainingAfter <= 1) { 
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

    public function generateVnpayUrl(int $orderId, float $amountPaid): string
    {
        $order = $this->orderRepository->getByConditions(['id' => $orderId]);
        if (!$order) {
            throw new \Exception('Đơn hàng không tồn tại');
        }

        $bill = $this->billRepository->getByConditions(['order_id' => $order->id]);
        if (!$bill) {
            throw new \Exception('Bill không tồn tại cho đơn hàng #' . $order->id);
        }

        $totalPaid = round((float)$this->billPaymentRepository->sumPaymentsForBill($bill->id), 2);
        $remainingAmount = round((float)$bill->final_amount - $totalPaid, 2);

        if ($remainingAmount <= 1) { 
            throw new \Exception('Bill đã được thanh toán đủ.');
        }
        if ($amountPaid <= 0 || $amountPaid > $remainingAmount) {
            throw new \Exception('Số tiền thanh toán không hợp lệ.');
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
        $vnp_BankCode = "NCB";
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

        if (!empty($vnp_BankCode)) {
            $inputData['vnp_BankCode'] = $vnp_BankCode;
        }

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

        return $vnp_Url;
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

        $amountPaid = round((float)$inputData['vnp_Amount'] / 100, 2); // Convert and round to 2 decimals
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

        if ($remainingAfter <= 1) { 
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
}
