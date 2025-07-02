<?php

namespace App\Repositories\BillPayments;

use App\Models\BillPayment;
use Illuminate\Support\Collection;

interface BillPaymentRepositoryInterface
{
    public function createData(array $data): BillPayment;
    
    public function getPaymentsByBillId(int $billId): Collection;

    public function getTotalPaid(int $billId): float;

    public function getByConditions(array $conditions);
    
    public function updateByConditions(array $conditions, array $updateData): bool;



}