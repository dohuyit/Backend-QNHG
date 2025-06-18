<?php

namespace App\Repositories\RolePermission;

use App\Models\RolePermission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class RolePermissionRepository implements RolePermissionRepositoryInterface
{
    public function createData(array $data): bool
    {
        return (bool) RolePermission::create($data);
    }

    public function exists(array $conditions): bool
    {
        return RolePermission::where($conditions)->exists();
    }

    public function getByConditions(array $conditions): ?RolePermission
    {
        return RolePermission::where($conditions)->first();
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        return (bool) RolePermission::where($conditions)->update($updateData);
    }

    public function getRolePermissionList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = RolePermission::query()
            ->with([
                'role:id,role_name,description',
                'permission:id,permission_name,description'
            ]);

        if (!empty($filter)) {
            $query = $this->filterRolePermissionList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterRolePermissionList(Builder $query, array $filter): Builder
    {
        if ($val = $filter['keyword'] ?? null) {
            $query->where(function ($q) use ($val) {
                $q->whereHas('role', function ($sub) use ($val) {
                    $sub->where('role_name', 'like', '%' . $val . '%')
                        ->orWhere('description', 'like', '%' . $val . '%');
                })->orWhereHas('permission', function ($sub) use ($val) {
                    $sub->where('permission_name', 'like', '%' . $val . '%')
                        ->orWhere('description', 'like', '%' . $val . '%');
                });
            });
        }
        if ($val = $filter['role_id'] ?? null) {
            $query->where('role_id', $val);
        }

        if ($val = $filter['permission_id'] ?? null) {
            $query->where('permission_id', $val);
        }

        return $query;
    }

    public function delete(RolePermission $rolePermission): bool
    {
        return $rolePermission->delete();
    }
}