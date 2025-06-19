<?php

namespace App\Repositories\Auth;

use App\Models\User;

class AuthClientRepository implements AuthClientRepositoryInterface
{
    public function getByConditions(array $conditions): ?User
    {
        return User::where($conditions)->first();
    }

    public function createData(array $data): User
    {
        $result = User::create($data);
        return $result;
    }

}
