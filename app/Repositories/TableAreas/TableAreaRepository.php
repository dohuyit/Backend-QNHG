<?php

namespace App\Repositories\TableAreas;

use App\Models\AreaTemplate;
use App\Models\TableArea;
use App\Models\Branch;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TableAreaRepository implements TableAreaRepositoryInterface
{
    public function getTableAreasByBranch(int $branchId, array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = TableArea::where('branch_id', $branchId);

        if (!empty($filter['query'])) {
            $query->where(function ($q) use ($filter) {
                $q->where('name', 'like', '%' . $filter['query'] . '%')
                    ->orWhere('description', 'like', '%' . $filter['query'] . '%');
            });
        }

        if (!empty($filter['status'])) {
            $query->where('status', $filter['status']);
        }

        $sortField = $filter['sort_by'] ?? 'created_at';
        $sortOrder = $filter['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);

        return $query->paginate($limit);
    }

    public function createTableAreaForBranch(array $data): ?TableArea
    {
        return TableArea::create([
            'branch_id' => $data['branch_id'],
            'area_template_id' => $data['area_template_id'] ?? null,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
            'capacity' => $data['capacity'] ?? 0,

        ]);
    }

    public function createTableAreaForAllBranches(array $data): array
    {
        $branches = Branch::all();
        $createdAreas = [];

        foreach ($branches as $branch) {
            $areaData = array_merge($data, ['branch_id' => $branch->id]);
            $createdAreas[] = $this->createTableAreaForBranch($areaData);
        }

        return $createdAreas;
    }

    public function updateTableAreaForBranch(array $data, TableArea $tableArea): bool
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }
        return $tableArea->update($data);
    }

    public function deleteTableAreaForBranch(TableArea $tableArea): bool
    {
        return $tableArea->delete();
    }

    public function getTableAreaDetail(TableArea $tableArea): ?TableArea
    {
        return $tableArea;
    }
}
