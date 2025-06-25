<?php

namespace App\Repositories\Categories;

use App\Models\Category;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = Category::where($conditions)->update($updateData);
        return (bool)$result;
    }

    public function createData(array $data): bool
    {
        $result = Category::create($data);
        return (bool)$result;
    }
    public function getByConditions(array $conditions): ?Category
    {
        $result = Category::where($conditions)->first();
        return $result;
    }

    public function getCategoryList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Category::query();

        if (!empty($filter)) {
            $query = $this->filterCategoryList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterCategoryList(Builder $query, array $filter = []): Builder
    {
        if (!empty($filter['name'])) {
            $query->where('name', 'like', '%' . $filter['name'] . '%');
        }

        if (array_key_exists('is_active', $filter)) {
            $query->where('is_active', $filter['is_active']);
        }

        return $query;
    }


    public function findOnlyTrashedById($id): ?Category
    {
        $result = Category::onlyTrashed()->where('id', $id)->firstOrFail();
        return $result;
    }

    public function getTrashCategoryList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Category::onlyTrashed();

        if (!empty($filter)) {
            $query = $this->filterCategoryList($query, $filter);
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($limit);
    }
    public function getCategoriesWithoutParent(): Collection
    {
        return Category::whereNull('parent_id')->get();
    }
    public function getChildrenByParentId(int $parentId): Collection
    {
        return Category::where('parent_id', $parentId)->get();
    }
    public function countByConditions(array $conditions = []): int
    {
        $query = Category::query();

        if (!empty($conditions)) {
            $this->filterCategoryList($query, $conditions);
        }
        return $query->count();
    }
}
