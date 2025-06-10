<?php
namespace App\Repositories\Dishes;

use App\Models\Dish;
use Illuminate\Pagination\LengthAwarePaginator;

interface DishRepositoryInterface
{
    public function getDishList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function createData(array $data): bool;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?Dish;
    public function getTrashDishList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findOnlyTrashedById($id): ?Dish;
    public function getDishesByCategoryId(int $id, array $filter = [], int $limit = 10): LengthAwarePaginator;
}