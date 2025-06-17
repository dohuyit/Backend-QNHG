<?php

namespace App\Repositories\ComboItems;

use App\Models\ComboItem;
use Illuminate\Pagination\LengthAwarePaginator;

interface ComboItemRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function createData(array $data): bool;
    public function getByConditions(array $conditions): ?ComboItem;
}