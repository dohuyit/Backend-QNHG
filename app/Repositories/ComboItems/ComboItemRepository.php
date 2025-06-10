<?php

namespace App\Repositories\ComboItems;

use App\Models\ComboItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ComboItemRepository implements ComboItemRepositoryInterface
{
    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = ComboItem::where($conditions)->update($updateData);
        return (bool)$result;
    }
    public function createData(array $data): bool
    {
        $result = ComboItem::create($data);
        return (bool)$result;
    }
    public function getByConditions(array $conditions): ?ComboItem
    {
        $result = ComboItem::where($conditions)->first();
        return $result;
    }

}