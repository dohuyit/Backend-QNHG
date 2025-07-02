<?php

namespace App\Services\BillPayments;

use App\Common\DataAggregate;
use App\Repositories\BillPayments\BillPaymentRepository;
use App\Repositories\Bills\BillRepositoryInterface;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class BillPaymentService
{
    protected BillPaymentRepository $billPaymentRepository;
    protected BillRepositoryInterface $billRepository;
    protected OrderRepositoryInterface $orderRepository;

    public function __construct(
        BillPaymentRepository $billPaymentRepository,
        BillRepositoryInterface $billRepository,
        OrderRepositoryInterface $orderRepository,
    ) {
        $this->billPaymentRepository = $billPaymentRepository;
        $this->billRepository = $billRepository;
        $this->orderRepository = $orderRepository;
    }
    private function syncBillAndOrderStatus(int $billId): void
    {
        $bill = $this->billRepository->getByConditions(['id' => $billId]);
        if (!$bill || !$bill->order_id) return;

        $order = $this->orderRepository->getByConditions(['id' => $bill->order_id]);
        if (!$order) return;

        $thisBillPaid = $this->billPaymentRepository->getTotalPaid($bill->id);
        $this->billRepository->updateByConditions(['id' => $bill->id], [
            'status' => $thisBillPaid >= $bill->final_amount ? 'paid' : 'unpaid',
        ]);


        $allBills = $this->billRepository->getAllByConditions(['order_id' => $order->id]);

        $totalPaid = 0;
        foreach ($allBills as $b) {
            $totalPaid += $this->billPaymentRepository->getTotalPaid($b->id);
        }

        $this->orderRepository->updateByConditions(['id' => $order->id], [
            'payment_status' => match (true) {
                $totalPaid <= 0 => 'unpaid',
                $totalPaid < $order->final_amount => 'partially_paid',
                default => 'paid',
            },
        ]);
    }

    public function createPayment(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $bill = $this->billRepository->getByConditions(['id' => $data['bill_id']]);
        if (!$bill) {
            $result->setMessage('Hóa đơn không tồn tại');
            return $result;
        }

        $order = $this->orderRepository->getByConditions(['id' => $bill->order_id]);
        if (!$order) {
            $result->setMessage('Đơn hàng không tồn tại');
            return $result;
        }

        $userId = Auth::id() ?? 1;

        $payment = $this->billPaymentRepository->createData([
            'bill_id'         => $bill->id,
            'payment_method'  => $data['payment_method'],
            'amount_paid'     => $data['amount_paid'],
            'transaction_ref' => $data['transaction_ref'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'payment_time'    => now(),
            'user_id'         => $userId,
        ]);

        $this->syncBillAndOrderStatus($bill->id);

        $result->setResultSuccess(
            message: 'Thanh toán thành công',
            data: ['payment' => $payment]
        );
        return $result;
    }

    public function getPaymentsByBill(int $billId): DataAggregate
    {
        $result = new DataAggregate();

        $bill = $this->billRepository->getByConditions(['id' => $billId]);
        if (!$bill) {
            $result->setMessage('Hóa đơn không tồn tại');
            return $result;
        }

        $payments = $this->billPaymentRepository->getPaymentsByBillId($billId);

        $result->setResultSuccess(data: ['payments' => $payments]);
        return $result;
    }
    public function updatePayment(int $paymentId, array $data): DataAggregate
    {
        $result = new DataAggregate();

        $payment = $this->billPaymentRepository->getByConditions(['id' => $paymentId]);

        if (!$payment) {
            $result->setMessage('Thanh toán không tồn tại');
            return $result;
        }
        $listDataUpdate = [
            'payment_method'  => $data['payment_method'],
            'amount_paid'     => $data['amount_paid'],
            'transaction_ref' => $data['transaction_ref'] ?? null,
            'notes'           => $data['notes'] ?? null,
            'payment_time'    => $data['payment_time'] ?? now(),
        ];

        $this->billPaymentRepository->updateByConditions(['id' => $paymentId], $listDataUpdate);

        $this->syncBillAndOrderStatus($payment->bill_id);

        $result->setResultSuccess(message: 'Cập nhật thanh toán thành công');
        return $result;
    }
}
