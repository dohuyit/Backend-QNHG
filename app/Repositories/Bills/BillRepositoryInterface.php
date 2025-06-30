<?php

namespace App\Repositories\Bills;

use Illuminate\Support\Collection;

interface BillRepositoryInterface
{
    public function getBillList(array $filter, int $limit);

    public function getByConditions(array $conditions);

    public function getAllByConditions(array $conditions): Collection;

    public function createData(array $data): bool;

    public function updateByConditions(array $conditions, array $data): bool;

    public function deleteByConditions(array $conditions): bool;    

}
