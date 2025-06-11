<?php

namespace App\Repositories\Auth;

use App\Models\AuthVerifyToken;

interface AuthVerifyTokenRepositoryInterface
{
    public function getByConditions(array $conditions): ?AuthVerifyToken;
    public function createData(array $data): bool;
    public function updateData($id, array $data): bool;
}
