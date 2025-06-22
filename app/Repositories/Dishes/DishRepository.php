<?php
namespace App\Repositories\Dishes;

use App\Models\Category;
use App\Models\Dish;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

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
            $query->where('selling_price',  '<=', $val);
        }

        if ($val = $filter['price_to'] ?? null) {
            $query->where('selling_price',  '<=', $val);
        }

        return $query;
    }
    public function getDishList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Dish::with('category');

        if(!empty($filter)){
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
        $query = Dish::onlyTrashed();

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
    public function getDishesByCategoryId(int $id, array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $category = Category::with('children')->where('id', $id)->first();
        if (!$category) {
            return new LengthAwarePaginator([], 0, $limit);
        }
        $categoryIds = $category->getAllChildrenIds();

        $query = Dish::query()->whereIn('category_id', $categoryIds);
        if (!empty($filter)) {
            $query = $this->filterDishList($query, $filter);
        }
        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

}