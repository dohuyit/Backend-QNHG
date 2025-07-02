<?php

namespace App\Repositories\Bills;

use App\Models\Bill;
use Illuminate\Support\Collection;

class BillRepository implements BillRepositoryInterface
{
    public function getBillList(array $filter, int $limit)
    {
        $query = Bill::query();

        if (!empty($filter['payment_status'])) {
            $query->where('payment_status', $filter['payment_status']);
        }

        if (!empty($filter['payment_method'])) {
            $query->where('payment_method', $filter['payment_method']);
        }

        if (!empty($filter['order_id'])) {
            $query->where('order_id', $filter['order_id']);
        }

        return $query->orderByDesc('id')->paginate($limit);
    }

    public function getByConditions(array $conditions)
    {
        return Bill::where($conditions)->first();
    }

    public function createData(array $data): bool
    {
        return Bill::create($data) ? true : false;
    }

    public function updateByConditions(array $conditions, array $data): bool
    {
        return Bill::where($conditions)->update($data);
    }

    public function deleteByConditions(array $conditions): bool
    {
        return Bill::where($conditions)->delete() > 0;
    }
    public function getAllByConditions(array $conditions): Collection
    {
        return Bill::where($conditions)->get();
    }
    
}
