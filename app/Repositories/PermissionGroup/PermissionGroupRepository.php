<?php

namespace App\Repositories\PermissionGroup;

use App\Models\PermissionGroup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class PermissionGroupRepository implements PermissionGroupRepositoryInterface
{
    public function createData(array $data): bool
    {
        $group = PermissionGroup::create($data);
        return (bool) $group;
    }

    public function getByConditions(array $conditions): ?PermissionGroup
    {
        return PermissionGroup::withTrashed()->where($conditions)->first();
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        return (bool) PermissionGroup::where($conditions)->update($updateData);
    }

    public function getPermissionGroupList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = PermissionGroup::query();

        if (!empty($filter)) {
            $query = $this->filterPermissionGroupList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterPermissionGroupList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['keyword'] ?? null) {
            $query->where(function ($q) use ($val) {
                $q->where('group_name', 'like', '%' . $val . '%')
                    ->orWhere('description', 'like', '%' . $val . '%');
            });
        }
        if ($val = $filter['group_name'] ?? null) {
            $query->where('group_name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['description'] ?? null) {
            $query->where('description', 'like', '%' . $val . '%');
        }

        return $query;
    }

    public function delete(PermissionGroup $group): bool
    {
        return $group->delete();
    }

    public function restore(PermissionGroup $group): bool
    {
        return $group->restore();
    }
}