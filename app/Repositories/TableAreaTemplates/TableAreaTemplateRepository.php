<?php

namespace App\Repositories\TableAreaTemplates;

use App\Models\AreaTemplate;
use Illuminate\Pagination\LengthAwarePaginator;

class TableAreaTemplateRepository implements TableAreaTemplateRepositoryInterface
{
    public function getListTableAreas(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = AreaTemplate::query();
        if (!empty($filter['query'])) {
            $query->where('name', 'like', '%' . $filter['query'] . '%')
                ->orWhere('description', 'like', '%' . $filter['query'] . '%');
        }
        $sortField = $filter['sort_by'] ?? 'created_at';
        $sortOrder = $filter['sort_order'] ?? 'desc';
        $query->orderBy($sortField, $sortOrder);
        return $query->paginate($limit);
    }

    public function getTableAreaDetail(string $slug): ?AreaTemplate
    {
        return AreaTemplate::where('slug', $slug)->first();
    }

    public function createTableArea(array $data): ?AreaTemplate
    {
        return AreaTemplate::create($data);
    }

    public function updateTableArea(array $data, AreaTemplate $areaTemplate): bool
    {
        return $areaTemplate->update($data);
    }

    public function deleteTableArea(string $slug): bool
    {
        $areaTemplate = AreaTemplate::where('slug', $slug)->first();
        if (!$areaTemplate) {
            return false;
        }
        return $areaTemplate->delete();
    }
}
