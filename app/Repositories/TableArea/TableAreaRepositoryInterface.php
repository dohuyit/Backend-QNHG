<?php

namespace App\Repositories\TableArea;

use App\Models\TableArea;
use Illuminate\Pagination\LengthAwarePaginator;

interface TableAreaRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;


    public function getTableAreaList(array $filter = [], int $limit = 10): LengthAwarePaginator;

    public function findById($id);
     public function create(array $data);
}
