<?php

namespace App\Repositories\UserRole;

use App\Models\UserRole;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRoleRepositoryInterface
{
    public function createData(array $data): bool;
    public function existsUserRole(int $userId, int $roleId): bool;

    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?UserRole;
    public function getUserRoleList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function delete(UserRole $userRole): bool;
}
