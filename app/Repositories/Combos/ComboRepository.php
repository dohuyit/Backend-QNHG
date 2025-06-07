<?php
namespace App\Repositories\Combos;

use App\Models\Combo;
use App\Models\ComboItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ComboRepository implements ComboRepositoryInterface
{
    private function filterComboList(Builder $query, array $filter = []): Builder
    {
        if($val = $filter['name'] ?? null){
            $query->where('name', 'like', "%{$val}%");
        }
         if($val = $filter['is_active'] ?? null){
            $query->where('is_active', 'like', "%{$val}%");
        }
        return $query;
    }
    public function getComboList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Combo::query();
        if(!empty($filter)) {
            $query = $this->filterComboList($query, $filter);
        }
        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }
    public function createData(array $data):bool
    {
        $result = Combo::create($data);
        return (bool)$result;
    }
    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = Combo::where($conditions)->update($updateData);
        return (bool)$result;
    }
    public function getByConditions(array $conditions): ?Combo
    {
        return Combo::where($conditions)->first();
    }
    public function getTrashComboList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = Combo::onlyTrashed();
        if(!empty($filter)) {
            $query = $this->filterComboList($query, $filter);
        }
        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }
    public function findOnlyTrashedBySlug($slug): ?Combo
    {
        return Combo::onlyTrashed()->where('slug', $slug)->first();
    }

}