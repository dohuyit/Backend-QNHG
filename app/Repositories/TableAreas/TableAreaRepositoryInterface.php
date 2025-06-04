<?php

namespace App\Repositories\TableAreas;

use App\Models\TableArea;
use Illuminate\Pagination\LengthAwarePaginator;

interface TableAreaRepositoryInterface
{
    public function getListTableAreas(array $params): LengthAwarePaginator;

    public function getTableAreaDetail(string $slug): ?TableArea;

    public function createTableArea(array $data): bool;

    public function updateTableArea(array $data, TableArea $tableArea): bool;
}
