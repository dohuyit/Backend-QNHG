<?php

namespace App\Repositories\Role;

use App\Models\Role;
use Illuminate\Pagination\LengthAwarePaginator;

interface RoleRepositoryInterface
{
    public function createData(array $data): bool;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?Role;
    public function getRoleList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function delete(Role $role): bool;
    public function isUsedInUserRoles(int $roleId): bool;

    public function isUsedInRolePermissions(int $roleId): bool;


}
