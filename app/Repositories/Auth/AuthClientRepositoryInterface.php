<?php

namespace App\Repositories\Auth;
use App\Models\User;

interface AuthClientRepositoryInterface
{
    public function getByConditions(array $conditions): ?User;
    public function createData(array $data): User;
}
