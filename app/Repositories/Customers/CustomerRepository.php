<?php

namespace App\Repositories\Customers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = Customer::where($conditions)->update($updateData);

        return (bool) $result;
    }

    public function getByConditions(array $conditions): ?Customer
    {

        $result = Customer::withTrashed()->where($conditions)->first();
        return $result;
    }


    public function getCustomerList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Customer::query();

        if (! empty($filter)) {
            $query = $this->filterCustomerList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterCustomerList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['full_name'] ?? null) {
            $query->where('full_name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['phone_number'] ?? null) {
            $query->where('phone_number', 'like', '%' . $val . '%');
        }

        if ($val = $filter['email'] ?? null) {
            $query->where('email', 'like', '%' . $val . '%');
        }

        if ($val = $filter['address'] ?? null) {
            $query->where('address', 'like', '%' . $val . '%');
        }

        if ($val = $filter['gender'] ?? null) {
            $query->where('gender', $val);
        }

        if ($val = $filter['city_id'] ?? null) {
            $query->where('city_id', $val);
        }

        if ($val = $filter['district_id'] ?? null) {
            $query->where('district_id', $val);
        }

        if ($val = $filter['ward_id'] ?? null) {
            $query->where('ward_id', $val);
        }

        if ($val = $filter['status_customer'] ?? null) {
            $query->where('status_customer', $val);
        }

        return $query;
    }


    public function findOnlyTrashedById($id): ?Customer
    {
        $result = Customer::onlyTrashed()->where('id', $id)->firstOrFail();

        return $result;
    }

    public function getTrashCustomerList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Customer::onlyTrashed();

        if (! empty($filter)) {
            $query = $this->filterCustomerList($query, $filter);
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($limit);
    }

    public function countByConditions(array $conditions = []): int
    {
        $query = Customer::query();

        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                $query->where($key, $value);
            }
        }

        return $query->count();
    }
}
