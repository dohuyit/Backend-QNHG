<?php

namespace App\Repositories\TableAreaTemplates;

use App\Models\AreaTemplate;
use Illuminate\Pagination\LengthAwarePaginator;

interface TableAreaTemplateRepositoryInterface
{
    public function getTableAreaDetail(string $slug): ?AreaTemplate;

    public function createTableArea(array $data): ?AreaTemplate;

    public function getListTableAreas(array $filter = [], int $limit = 10): LengthAwarePaginator;

    public function updateTableArea(array $data, AreaTemplate $areaTemplate): bool;

    public function deleteTableArea(string $slug): bool;
}
