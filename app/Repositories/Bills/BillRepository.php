<?php

namespace App\Repositories\Bills;

use App\Models\Bill;
use Illuminate\Support\Collection;

class BillRepository implements BillRepositoryInterface
{

    public function getByConditions(array $conditions)
    {
        return Bill::where($conditions)->first();
    }

    public function updateByConditions(array $conditions, array $data): bool
    {
        return Bill::where($conditions)->update($data);
    }
     public function createDataAndReturn(array $data): Bill
    {
        return Bill::create($data);
    }

    public function firstOrCreate(array $condition, array $data): Bill
    {
        return Bill::firstOrCreate($condition, $data);
    }
}
