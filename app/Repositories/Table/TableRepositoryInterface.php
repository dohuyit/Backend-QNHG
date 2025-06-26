<?php

namespace App\Repositories\Table;

use App\Models\Table;
use Illuminate\Pagination\LengthAwarePaginator;

interface TableRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;

    public function getTableList(array $filter = [], int $limit = 10): LengthAwarePaginator;

    public function findById($id);

    public function createTable(array $data);

    public function deleteTable($id): bool;

    public function countByConditions(array $conditions): int;

}