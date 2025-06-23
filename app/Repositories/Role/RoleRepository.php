<?php

namespace App\Repositories\Role;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RoleRepository implements RoleRepositoryInterface
{
    public function createData(array $data): bool
    {
        $role = Role::create($data);
        return (bool) $role;
    }

    public function getByConditions(array $conditions): ?Role
    {
        return Role::withTrashed()->where($conditions)->first();
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        return (bool) Role::where($conditions)->update($updateData);
    }

    public function getRoleList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Role::query();

        if (!empty($filter)) {
            $query = $this->filterRoleList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterRoleList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['keyword'] ?? null) {
            $query->where(function ($q) use ($val) {
                $q->where('role_name', 'like', '%' . $val . '%')
                    ->orWhere('description', 'like', '%' . $val . '%');
            });
        }
        if ($val = $filter['role_name'] ?? null) {
            $query->where('role_name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['description'] ?? null) {
            $query->where('description', 'like', '%' . $val . '%');
        }

        return $query;
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }
    public function isUsedInUserRoles(int $roleId): bool
    {
        return DB::table('user_roles')->where('role_id', $roleId)->exists();
    }

    public function isUsedInRolePermissions(int $roleId): bool
    {
        return DB::table('role_permissions')->where('role_id', $roleId)->exists();
    }

}