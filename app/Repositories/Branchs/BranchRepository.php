<?php

namespace App\Repositories\Branchs;

use App\Models\Branch;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class BranchRepository implements BranchRepositoryInterface
{

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = Branch::where($conditions)->update($updateData);
        return (bool)$result;
    }

    public function createData(array $data): bool
    {
        $result = Branch::create($data);
        return (bool)$result;
    }


    public function getByConditions(array $conditions): ?Branch
    {
        $result = Branch::withTrashed()->where($conditions)->first();
        return $result;
    }


    public function getBranchList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Branch::query();

        if (!empty($filter)) {
            $query = $this->filterBranchList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterBranchList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['name'] ?? null) {
            $query->where('name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['city_id'] ?? null) {
            $query->where('city_id', $val);
        }

        if ($val = $filter['district_id'] ?? null) {
            $query->where('district_id', $val);
        }

        if ($val = $filter['status'] ?? null) {
            $query->where('status', $val);
        }

        return $query;
    }

    public function findOnlyTrashedBySlug($slug): ?Branch
    {
        $result = Branch::onlyTrashed()->where('slug', $slug)->firstOrFail();
        return $result;
    }

    public function getTrashBranchList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Branch::onlyTrashed();

        if (!empty($filter)) {
            $query = $this->filterBranchList($query, $filter);
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($limit);
    }
}
