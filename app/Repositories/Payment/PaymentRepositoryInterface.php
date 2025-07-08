<?php

namespace App\Repositories\Payment;

use App\Models\Bill;
use App\Models\BillPayment;

interface PaymentRepositoryInterface
{
    // Bill methods
    public function getBillByConditions(array $conditions): ?Bill;

    public function updateBillByConditions(array $conditions, array $data): bool;

    public function createBill(array $data): Bill;

    public function firstOrCreateBill(array $condition, array $data): Bill;

    // BillPayment methods
    public function createPayment(array $data): BillPayment;

    public function sumPaymentsForBill(int $billId): float;

    public function getPaymentByConditions(array $conditions): ?BillPayment;
}
