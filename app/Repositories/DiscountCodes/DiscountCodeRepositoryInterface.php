<?php
namespace App\Repositories\DiscountCodes;

use App\Models\DiscountCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DiscountCodeRepositoryInterface
{
    public function getDiscountCodeList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function createData(array $data): bool;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getByConditions(array $conditions): ?DiscountCode;
    public function forceDelete(DiscountCode $discountCode): bool;
    public function countByConditions(array $conditions): int;

}