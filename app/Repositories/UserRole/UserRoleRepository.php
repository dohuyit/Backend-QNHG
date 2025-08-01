<?php

namespace App\Repositories\UserRole;

use App\Models\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class UserRoleRepository implements UserRoleRepositoryInterface
{
    public function createData(array $data): bool
    {
        $userRole = UserRole::create($data);
        return (bool) $userRole;
    }

    public function existsUserRole(int $userId, int $roleId): bool
    {
        return UserRole::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->exists();
    }

    public function isDuplicateUserRoleExceptId(int $userId, int $roleId, int $excludeId): bool
    {
        return UserRole::where('user_id', $userId)
            ->where('role_id', $roleId)
            ->where('id', '!=', $excludeId)
            ->exists();
    }
    public function getByConditions(array $conditions): ?UserRole
    {
        return UserRole::where($conditions)->first();
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        return (bool) UserRole::where($conditions)->update($updateData);
    }

    public function getUserRoleList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = UserRole::query()
            ->with(['user:id,username,email,status,phone_number', 'role:id,role_name,description']);

        if (!empty($filter)) {
            $query = $this->filterUserRoleList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterUserRoleList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['keyword'] ?? null) {
            $query->whereHas('user', function ($q) use ($val) {
                $q->where('username', 'like', '%' . $val . '%')
                    ->orWhere('email', 'like', '%' . $val . '%')
                    ->orWhere('phone_number', 'like', '%' . $val . '%');
            })->orWhereHas('role', function ($q) use ($val) {
                $q->where('role_name', 'like', '%' . $val . '%')
                    ->orWhere('description', 'like', '%' . $val . '%');
            });
        }

        if ($val = $filter['user_id'] ?? null) {
            $query->where('user_id', $val);
        }

        if ($val = $filter['role_id'] ?? null) {
            $query->where('role_id', $val);
        }

        return $query;
    }

    public function delete(UserRole $userRole): bool
    {
        return $userRole->delete();
    }
}