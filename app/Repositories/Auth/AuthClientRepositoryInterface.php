<?php

namespace App\Repositories\Auth;
use App\Models\Customer;

interface AuthClientRepositoryInterface
{
    public function getByConditions(array $conditions): ?Customer;
    public function createData(array $data): Customer;
}
