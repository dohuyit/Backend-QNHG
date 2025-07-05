<?php

namespace App\Repositories\Bills;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function getBillList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Bill::query()->with(['order', 'user']); // Eager load order và user

        if (!empty($filter)) {
            $query = $this->filterBillList($query, $filter);
        }

        return $query->orderBy('issued_at', 'desc')->paginate($limit);
    }

    private function filterBillList(Builder $query, array $filter = []): Builder
    {
        if (!empty($filter['bill_code'])) {
            $query->where('bill_code', 'like', '%' . $filter['bill_code'] . '%');
        }

        if (!empty($filter['order_id'])) {
            $query->where('order_id', $filter['order_id']);
        }

        if (!empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        if (!empty($filter['user_id'])) {
            $query->where('user_id', $filter['user_id']);
        }

        if (!empty($filter['issued_from'])) {
            $query->whereDate('issued_at', '>=', $filter['issued_from']);
        }

        if (!empty($filter['issued_to'])) {
            $query->whereDate('issued_at', '<=', $filter['issued_to']);
        }

        return $query;
    }

    public function countByConditions(array $conditions = []): int
    {
        $query = Bill::query();

        if (!empty($conditions)) {
            $this->filterBillList($query, $conditions);
        }
        return $query->count();
    }
}
