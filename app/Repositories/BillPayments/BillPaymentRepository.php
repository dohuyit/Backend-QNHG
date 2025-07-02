<?php

namespace App\Repositories\BillPayments;

use App\Models\BillPayment;
use Illuminate\Database\Eloquent\Collection;

class BillPaymentRepository implements BillPaymentRepositoryInterface
{
    public function createData(array $data): BillPayment
    {
        return BillPayment::create($data);
    }

    public function getPaymentsByBillId(int $billId): Collection
    {
        return BillPayment::where('bill_id', $billId)
            ->orderBy('payment_time', 'desc')
            ->get();
    }

    public function getTotalPaid(int $billId): float
    {
        return BillPayment::where('bill_id', $billId)->sum('amount_paid') ?? 0;
    }

    public function getByConditions(array $conditions)
    {
        return BillPayment::where($conditions)->first();
    }

        public function updateByConditions(array $conditions, array $data): bool
    {
        return BillPayment::where($conditions)->update($data);
    }
    
}
