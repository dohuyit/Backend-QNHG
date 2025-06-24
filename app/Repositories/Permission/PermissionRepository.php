<?php

namespace App\Repositories\Permission;

use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function createData(array $data): bool
    {
        $permission = Permission::create($data);
        return (bool) $permission;
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        return (bool) Permission::where($conditions)->update($updateData);
    }

    public function getByConditions(array $conditions): ?Permission
    {
        return Permission::withTrashed()->where($conditions)->first();
    }

    public function getPermissionList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Permission::query()
            ->leftJoin('permission_groups', 'permissions.permission_group_id', '=', 'permission_groups.id')
            ->select([
                'permissions.*',
                'permission_groups.group_name as permission_group_name',
                'permission_groups.description as permission_group_description',
            ]);

        if (!empty($filter)) {
            $query = $this->filterPermissionList($query, $filter);
        }

        return $query->orderBy('permissions.created_at', 'desc')->paginate($limit);
    }

    private function filterPermissionList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['keyword'] ?? null) {
            $query->where(function ($q) use ($val) {
                $q->where('permissions.permission_name', 'like', '%' . $val . '%')
                    ->orWhere('permissions.description', 'like', '%' . $val . '%')
                    ->orWhere('permission_groups.group_name', 'like', '%' . $val . '%')
                    ->orWhere('permission_groups.description', 'like', '%' . $val . '%');
            });
        }

        if ($val = $filter['permission_name'] ?? null) {
            $query->where('permission_name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['description'] ?? null) {
            $query->where('description', 'like', '%' . $val . '%');
        }

        if ($val = $filter['permission_group_id'] ?? null) {
            $query->where('permission_group_id', $val);
        }

        return $query;
    }

    public function isUsedInRolePermissions(int $permissionId): bool
    {
        return RolePermission::where('permission_id', $permissionId)->exists();
    }

    public function forceDelete(Permission $permission): bool
    {
        return $permission->forceDelete();
    }

}