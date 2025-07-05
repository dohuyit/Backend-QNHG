<?php

namespace App\Services\OrderPayments;

use App\Common\DataAggregate;
use App\Repositories\BillPayments\BillPaymentRepositoryInterface;
use App\Repositories\Bills\BillRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Services\PaymentGateways\MomoService;
use App\Services\PaymentGateways\VnpayService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderPaymentService
{
    protected OrderRepositoryInterface $orderRepository;
    protected BillRepositoryInterface $billRepository;
    protected BillPaymentRepositoryInterface $billPaymentRepository;
    protected VnpayService $vnpayService;
    protected MomoService $momoService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        BillRepositoryInterface $billRepository,
        BillPaymentRepositoryInterface $billPaymentRepository,
        VnpayService $vnpayService,
        MomoService $momoService
    ) {
        $this->orderRepository = $orderRepository;
        $this->billRepository = $billRepository;
        $this->billPaymentRepository = $billPaymentRepository;
        $this->vnpayService  = $vnpayService;
        $this->momoService = $momoService;
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
            $paymentResult = $this->vnpayService->generateVnpayUrl($order->id, $amountPaidForVnpay);

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
            $amountPaidForVnpay = ($amountPaid > 0) ? $amountPaid : $remaining;

            if ($amountPaidForVnpay < 10000) {
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
            $paymentResult = $this->momoService->generateMomoUrl($order->id, $amountPaidForVnpay);

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
}
