<?php

namespace App\Repositories\Dishes;

use App\Models\Category;
use App\Models\Dish;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class DishRepository implements DishRepositoryInterface
{

    private function filterDishList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['name'] ?? null) {
            $query->where('name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['category_id'] ?? null) {
            $query->where('category_id', $val);
        }

        if ($val = $filter['status'] ?? null) {
            $query->where('status', $val);
        }

        if ($val = $filter['is_featured'] ?? null) {
            $query->where('is_featured', $val);
        }

        if ($val = $filter['price_from'] ?? null) {
            $query->where('selling_price', '>=', $val);
        }
        if ($val = $filter['price_to'] ?? null) {
            $query->where('selling_price', '<=', $val);
        }


        return $query;
    }
    public function getDishList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Dish::with('category');

        if (!empty($filter)) {
            $result = $this->filterDishList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }
    public function createData(array $data): bool
    {
        $result = Dish::create($data);
        return (bool)$result;
    }
    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = Dish::where($conditions)->update($updateData);
        return (bool)$result;
    }
    public function getByConditions(array $conditions): ?Dish
    {
        $result = Dish::where($conditions)->first();
        return $result;
    }
    function getTrashDishList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Dish::onlyTrashed()->with('category');

        if (!empty($filter)) {
            $query = $this->filterDishList($query, $filter);
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($limit);
    }
    public function findOnlyTrashedById($id): ?Dish
    {
        $result = Dish::onlyTrashed()->where('id', $id)->firstOrFail();
        return $result;
    }

    public function getFeaturedDishes(): Collection
    {
        return Dish::where('is_featured', true)
            ->where('is_active', true)
            ->with('category') // optional
            ->get();
    }

    public function getByCategoryId($categoryId): Collection
    {
        return Dish::where('category_id', $categoryId)
            ->where('is_active', true)
            ->with('category')
            ->get();
    }
    public function countByConditions(array $conditions = []): int
    {
        $query = Dish::query();

        if (!empty($conditions)) {
            $this->filterDishList($query, $conditions);
        }
        return $query->count();
    }

    public function getAllActiveDishes(): Collection
    {
        return Dish::where('status', 1)
            ->with('category')
            ->get();
    }

    public function getLatestActiveDishes(int $limit = 10): Collection
    {
        return Dish::where('status', 1)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getActiveDishDetail(int $id): ?Dish
    {
        return Dish::where('status', 1)->find($id);
    }
}
