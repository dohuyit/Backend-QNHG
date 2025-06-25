<?php

namespace App\Repositories\Categories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function createData(array $data): bool;
    public function getByConditions(array $conditions): ?Category;
    public function getCategoryList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function getTrashCategoryList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findOnlyTrashedById($id): ?Category;
    public function getCategoriesWithoutParent(): Collection;
    public function getChildrenByParentId(int $parentId): Collection;
    public function countByConditions(array $conditions): int;

}