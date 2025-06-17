<?php

namespace App\Repositories\PermissionGroup;

use App\Models\PermissionGroup;
use Illuminate\Pagination\LengthAwarePaginator;

interface PermissionGroupRepositoryInterface
{
    public function createData(array $data): bool;
    public function getByConditions(array $conditions): ?PermissionGroup;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getPermissionGroupList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function delete(PermissionGroup $group): bool;
    public function restore(PermissionGroup $group): bool;
}
