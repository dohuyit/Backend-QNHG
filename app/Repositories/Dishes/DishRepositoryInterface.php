<?php

namespace App\Repositories\Dishes;

use App\Models\Dish;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface DishRepositoryInterface
{
    // Admin
    public function getDishList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function createData(array $data): bool;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?Dish;
    public function getTrashDishList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findOnlyTrashedById($id): ?Dish;
    public function getFeaturedDishes(): Collection;
    public function getByCategoryId($categoryId): Collection;
    public function countByConditions(array $conditions): int;

    // Client
    public function getAllActiveDishes(): Collection;
    public function getLatestActiveDishes(int $limit = 10): Collection;
}
