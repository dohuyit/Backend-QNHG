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
    
    public function sumPaymentsForBill(int $billId): float
    {
        return BillPayment::where('bill_id', $billId)->sum('amount_paid');
    }
        public function getByConditions(array $conditions): ?BillPayment
    {
        $result = BillPayment::where($conditions)->first();
        return $result;
    }
    
}
