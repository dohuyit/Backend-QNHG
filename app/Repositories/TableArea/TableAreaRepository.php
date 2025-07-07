<?php

namespace App\Repositories\TableArea;

use App\Models\TableArea;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TableAreaRepository implements TableAreaRepositoryInterface
{
    protected $model;

    public function __construct(TableArea $model)
    {
        $this->model = $model;
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = TableArea::where($conditions)->update($updateData);
        return (bool) $result;
    }


    public function getTableAreaList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = TableArea::query();

        if (!empty($filter)) {
            $query = $this->filterTableAreaList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterTableAreaList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['name'] ?? null) {
            $query->where('name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['status'] ?? null) {
            $query->where('status', $val);
        }

        if ($val = $filter['capacity'] ?? null) {
            $query->where('capacity', $val);
        }

        return $query;
    }

    public function findById($id)
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function countByConditions(array $conditions = []): int
    {
        $query = TableArea::query();

        if (!empty($conditions)) {
            $query = $this->filterTableAreaList($query, $conditions);
        }

        return $query->count();
    }
}
