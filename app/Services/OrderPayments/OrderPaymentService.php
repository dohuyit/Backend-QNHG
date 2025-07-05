<?php

namespace App\Services\OrderPayments;

use App\Common\DataAggregate;
use App\Repositories\BillPayments\BillPaymentRepositoryInterface;
use App\Repositories\Bills\BillRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;

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
                    $result->setMessage(message: 'Đơn hàng đã huỷ. Bill cũng được cập nhật trạng thái huỷ, không thể thanh toán.');
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
            $subTotal = (float)$order->total_amount;
            $discount = isset($data['discount_amount']) ? (float)$data['discount_amount'] : 0.0;
            $deliveryFee = isset($data['delivery_fee']) ? (float)$data['delivery_fee'] : 0.0;
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

        $totalPaid = (float)$this->billPaymentRepository->sumPaymentsForBill($bill->id);
        $finalAmount = (float)$bill->final_amount;

        if ($totalPaid >= $finalAmount) {
            $result->setMessage('Bill đã được thanh toán đủ.');
            return $result;
        }

        $remaining = round($finalAmount - $totalPaid, 2);
        $amountPaid = (float)$data['amount_paid'];

        if ($amountPaid > $remaining) {
            $result->setMessage('Số tiền thanh toán vượt quá số tiền còn phải trả: ' . number_format($remaining, 2) . ' VND');
            return $result;
        }

        $payment = $this->billPaymentRepository->createData([
            'bill_id' => $bill->id,
            'payment_method' => $data['payment_method'],
            'amount_paid' => $amountPaid,
            'user_id' => $data['user_id'] ?? Auth::id(),
            'notes' => $data['notes'] ?? null,
        ]);

        $totalPaidAfter = (float)$this->billPaymentRepository->sumPaymentsForBill($bill->id);
        $remainingAfter = round($finalAmount - $totalPaidAfter, 2);

        if ($remainingAfter <= 0) {
            $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'paid']);
            $message = 'Đã thanh toán đủ, bill hoàn tất.';
        } else {
            $this->billRepository->updateByConditions(['id' => $bill->id], ['status' => 'unpaid']);
            $message = 'Thanh toán một phần. Còn thiếu: ' . number_format($remainingAfter, 2) . ' VND';
        }

        $result->setResultSuccess(
            message: $message,
            data: [
                'bill' => $bill->fresh(),
                'payment' => $payment,
            ]
        );
        return $result;
    }

}
