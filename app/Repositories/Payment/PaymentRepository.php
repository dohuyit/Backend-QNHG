<?php

namespace App\Repositories\Payment;

use App\Models\Bill;
use App\Models\BillPayment;

class PaymentRepository implements PaymentRepositoryInterface
{
    // Bill methods
    public function getBillByConditions(array $conditions): ?Bill
    {
        return Bill::where($conditions)->first();
    }

    public function updateBillByConditions(array $conditions, array $data): bool
    {
        return Bill::where($conditions)->update($data);
    }

    public function createBill(array $data): Bill
    {
        return Bill::create($data);
    }

    public function firstOrCreateBill(array $condition, array $data): Bill
    {
        return Bill::firstOrCreate($condition, $data);
    }

    // BillPayment methods
    public function createPayment(array $data): BillPayment
    {
        return BillPayment::create($data);
    }

    public function sumPaymentsForBill(int $billId): float
    {
        return BillPayment::where('bill_id', $billId)->sum('amount_paid');
    }

    public function getPaymentByConditions(array $conditions): ?BillPayment
    {
        return BillPayment::where($conditions)->first();
    }
}
