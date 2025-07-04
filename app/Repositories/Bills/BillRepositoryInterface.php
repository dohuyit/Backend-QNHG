<?php

namespace App\Repositories\Bills;

use App\Models\Bill;
use Illuminate\Support\Collection;

interface BillRepositoryInterface
{
    public function getByConditions(array $conditions);

    public function updateByConditions(array $conditions, array $data): bool;

    public function firstOrCreate(array $condition, array $data): Bill;
    
    public function createDataAndReturn(array $data): Bill;



}
