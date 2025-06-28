<?php

namespace App\Repositories\Auth;

use App\Models\Customer;

class AuthClientRepository implements AuthClientRepositoryInterface
{
    public function getByConditions(array $conditions): ?Customer
    {
        return Customer::where($conditions)->first();
    }

    public function createData(array $data): Customer
    {
        $result = Customer::create($data);
        return $result;
    }

}
