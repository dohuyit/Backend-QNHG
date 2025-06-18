<?php

namespace App\Repositories\RolePermission;

use App\Models\RolePermission;
use Illuminate\Pagination\LengthAwarePaginator;

interface RolePermissionRepositoryInterface
{
    public function createData(array $data): bool;
    public function exists(array $conditions): bool;
    public function getByConditions(array $conditions): ?RolePermission;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getRolePermissionList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function delete(RolePermission $rolePermission): bool;
}
