<?php
namespace App\Repositories\Categories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CategoryRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function createData(array $data): bool;
    public function getByConditions(array $conditions): ?Category;
    public function getCategoryList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function getTrashCategoryList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findOnlyTrashedById($id): ?Category;
}