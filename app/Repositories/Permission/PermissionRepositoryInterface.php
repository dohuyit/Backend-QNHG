<?php

namespace App\Repositories\Permission;

use App\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;

interface PermissionRepositoryInterface
{
    public function createData(array $data): bool;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?Permission;
    public function getPermissionList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function delete(Permission $permission): bool;

    public function restore(Permission $permission): bool;
}
