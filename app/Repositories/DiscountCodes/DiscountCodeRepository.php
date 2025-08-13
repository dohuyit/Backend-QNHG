<?php

namespace App\Repositories\DiscountCodes;

use App\Models\DiscountCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DiscountCodeRepository implements DiscountCodeRepositoryInterface
{
    private function filterDiscountCodeList($query, array $filter = []): \Illuminate\Database\Eloquent\Builder
    {
        if (!empty($filter['query'])) {
            $search = $filter['query'];
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', '%' . $search . '%')
                    ->orWhere('type', 'like', '%' . $search . '%');
            });
        } else {
            if ($val = $filter['code'] ?? null) {
                $query->where('code', 'like', '%' . $val . '%');
            }

            if ($val = $filter['type'] ?? null) {
                $query->where('type', $val);
            }
        }
        if ($val = $filter['code'] ?? null) {
            $query->where('code', 'like', '%' . $val . '%');
        }

        if ($val = $filter['type'] ?? null) {
            $query->where('type', $val);
        }

        if (isset($filter['is_active'])) {
            $query->where('is_active', $filter['is_active']);
        }


        if ($val = $filter['start_date'] ?? null) {
            $query->where('start_date', '>=', $val);
        }

        if ($val = $filter['end_date'] ?? null) {
            $query->where('end_date', '<=', $val);
        }

        return $query;
    }
    public function getDiscountCodeList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = DiscountCode::query();

        if (!empty($filter)) {
            $query = $this->filterDiscountCodeList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    public function createData(array $data): bool
    {
        $result = DiscountCode::create($data);
        return (bool)$result;
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = DiscountCode::where($conditions)->update($updateData);
        return (bool)$result;
    }

    public function getByConditions(array $conditions): ?DiscountCode
    {
        return DiscountCode::where($conditions)->first();
    }
    public function forceDelete(DiscountCode $discountCode): bool
    {
        return $discountCode->forceDelete();
    }
    public function countByConditions(array $conditions = []): int
    {
        $query = DiscountCode::query();

        if (!empty($conditions)) {
            $query = $this->filterDiscountCodeList($query, $conditions);
        }
        return $query->count();
    }
}
