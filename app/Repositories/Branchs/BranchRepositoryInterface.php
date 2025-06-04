<?php

namespace App\Repositories\Branchs;

use App\Models\Branch;
use Illuminate\Pagination\LengthAwarePaginator;

interface BranchRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function createData(array $data): bool;
    public function getByConditions(array $conditions): ?Branch;
    public function getBranchList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function getTrashBranchList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findOnlyTrashedBySlug($slug): ?Branch;
}
