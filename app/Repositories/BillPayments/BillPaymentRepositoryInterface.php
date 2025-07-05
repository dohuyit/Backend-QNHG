<?php

namespace App\Repositories\BillPayments;

use App\Models\BillPayment;
use Illuminate\Support\Collection;

interface BillPaymentRepositoryInterface
{
    public function createData(array $data): BillPayment;

    public function sumPaymentsForBill(int $billId): float;

    public function getByConditions(array $conditions): ?BillPayment;



}