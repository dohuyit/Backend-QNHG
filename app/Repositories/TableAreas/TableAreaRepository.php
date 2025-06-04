<?php

namespace App\Repositories\TableAreas;

use App\Models\TableArea;
use Illuminate\Pagination\LengthAwarePaginator;

class TableAreaRepository implements TableAreaRepositoryInterface
{
    public function getListTableAreas(array $params): LengthAwarePaginator
    {
        $query = TableArea::query();

        if (isset($params['query'])) {
            $query->where('name', 'like', '%'.$params['query'].'%')
                ->orWhere('description', 'like', '%'.$params['query'].'%');
        }
        if (isset($params['branch_id'])) {
            $query->where('branch_id', $params['branch_id']);
        }
        if (isset($params['status'])) {
            $query->where('status', $params['status']);
        }

        // Add sorting based on parameters, default to created_at desc
        $sortField = $params['sort_by'] ?? 'created_at';
        $sortOrder = $params['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($params['limit'] ?? 10);
    }

    public function getTableAreaDetail(string $slug): ?TableArea
    {
        return TableArea::where('slug', $slug)->first();
    }

    public function createTableArea(array $data): bool
    {
        return TableArea::create($data) ? true : false;
    }

    public function updateTableArea(array $data, TableArea $tableArea): bool
    {
        return $tableArea->update($data);
    }
}
