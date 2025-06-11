<?php

namespace App\Repositories\Users;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class  UserRepository implements UserRepositoryInterface
{
    public function createData(array $data): bool
    {
        $result = User::create($data);
        return (bool) $result;
    }
    public function getByConditions(array $conditions): ?User
    {
        return User::where($conditions)->first();
    }

    public function updateByConditions(array $conditions, array $updateData): bool
    {
        $result = User::where($conditions)->update($updateData);
        return (bool)$result;
    }
    public function getUserList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = User::with('branch');

        if (!empty($filter)) {
            $query = $this->filterUserList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }
    private function filterUserList(Builder $query, array $filter = []): Builder
    {
        if ($val = $filter['username'] ?? null) {
            $query->where('username', 'like', '%' . $val . '%');
        }

        if ($val = $filter['full_name'] ?? null) {
            $query->where('full_name', 'like', '%' . $val . '%');
        }

        if ($val = $filter['email'] ?? null) {
            $query->where('email', 'like', '%' . $val . '%');
        }

        if ($val = $filter['phone_number'] ?? null) {
            $query->where('phone_number', 'like', '%' . $val . '%');
        }

        if ($val = $filter['status'] ?? null) {
            $query->where('status', $val);
        }

        if ($val = $filter['branch_id'] ?? null) {
            $query->where('branch_id', $val);
        }

        return $query;
    }
    public function getTrashUserList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = User::onlyTrashed()->with('branch');

        if (!empty($filter)) {
            $query = $this->filterUserList($query, $filter);
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($limit);
    }
    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function deleteById(int $id): bool
    {
        return User::destroy($id) > 0;
    }
}
