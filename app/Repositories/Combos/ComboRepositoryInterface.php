<?php
namespace App\Repositories\Combos;

use App\Models\Combo;
use Illuminate\Pagination\LengthAwarePaginator;

interface ComboRepositoryInterface
{
    public function getComboList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function createData(array $data): bool;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?Combo;
    public function getTrashComboList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findOnlyTrashedById($id): ?Combo;
    public function countByConditions(array $conditions): int;
}