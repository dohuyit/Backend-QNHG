<?php

namespace App\Repositories\Auth;

use App\Models\AuthVerifyToken;

class AuthVerifyTokenRepository implements AuthVerifyTokenRepositoryInterface
{
    public function getByConditions(array $conditions): ?AuthVerifyToken
    {
        return AuthVerifyToken::where($conditions)->first();
    }

    public function createData(array $data): bool
    {
        $result = AuthVerifyToken::create($data);
        return (bool) $result;
    }

    public function updateData($id, array $data): bool
    {
        return AuthVerifyToken::where('id', $id)->update($data);
    }
}
