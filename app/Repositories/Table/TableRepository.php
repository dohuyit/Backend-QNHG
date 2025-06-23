<?php

namespace App\Repositories\Table;

use App\Models\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TableRepository implements TableRepositoryInterface
{
    protected $model;

    public function __construct(Table $model)
    {
        $this->model = $model;
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = Table::where($conditions)->update($updateData);
        return (bool) $result;
    }

    public function getTableList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Table::with('tableArea');

        if (!empty($filter)) {
            $query = $this->filterTableList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterTableList(Builder $query, array $filter = []): Builder
    {

        if ($val = $filter['status'] ?? null) {
            $query->where('status', $val);
        }

        if ($val = $filter['capacity'] ?? null) {
            $query->where('capacity', $val);
        }

        if ($val = $filter['table_area_id'] ?? null) {
            $query->where('table_area_id', $val);
        }

        return $query;
    }

    public function findById($id)
    {
        return $this->model->with('tableArea')->findOrFail($id);
    }

    public function createTable(array $data)
    {
        return $this->model->create($data);
    }

    public function deleteTable($id): bool
    {
        $table = $this->model->find($id);
        if (!$table) {
            return false;
        }
        return $table->delete();
    }
}
