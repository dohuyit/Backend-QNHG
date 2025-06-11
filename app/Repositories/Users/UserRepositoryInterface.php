<?php

namespace App\Repositories\Users;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function createData(array $data): bool;
    public function getByConditions(array $conditions): ?User;
    public function updateByConditions(array $conditions, array $updateData): bool;
    public function getUserList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function getTrashUserList(array $filter = [], int $limit = 10): LengthAwarePaginator;
    public function findById(int $id): ?User;
    public function deleteById(int $id): bool;
}
