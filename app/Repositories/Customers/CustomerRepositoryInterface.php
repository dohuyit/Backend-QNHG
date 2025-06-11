<?php

namespace App\Repositories\Customers;

use App\Models\Customer;
use Illuminate\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;

    public function getByConditions(array $conditions): ?Customer;

    public function getCustomerList(array $filter = [], int $limit = 10): LengthAwarePaginator;

    public function getTrashCustomerList(array $filter = [], int $limit = 10): LengthAwarePaginator;

    public function findOnlyTrashedById($id): ?Customer;
}
