<?php

namespace App\Repositories\Users;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
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
        return (bool) $result;
    }

    public function isUserActive(int $userId): bool
    {
        return User::where('id', $userId)
            ->where('status', User::STATUS_ACTIVE)
            ->exists();
    }

    public function getUserList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = User::query(); // đảm bảo query khởi tạo đúng

        if (!empty($filter)) {
            $query = $this->filterUserList($query, $filter);
        }

        return $query->orderBy('created_at', 'desc')->paginate($limit);
    }

    private function filterUserList(Builder $query, array $filter = []): Builder
    {

        if (
            !isset($filter['username']) &&
            !isset($filter['full_name']) &&
            !isset($filter['email']) &&
            !isset($filter['phone_number']) &&
            ($val = $filter['keyword'] ?? $filter['query'] ?? null) &&
            trim($val) !== ''
        ) {
            $query->where(function ($q) use ($val) {
                $q->where('username', 'like', '%' . $val . '%')
                    ->orWhere('full_name', 'like', '%' . $val . '%')
                    ->orWhere('email', 'like', '%' . $val . '%')
                    ->orWhere('phone_number', 'like', '%' . $val . '%');
            });
        }

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

        return $query;
    }


    public function getTrashUserList(array $filter = [], int $limit = 10): LengthAwarePaginator
    {
        $query = User::onlyTrashed();

        if (!empty($filter)) {
            $query = $this->filterUserList($query, $filter);
        }

        return $query->orderBy('deleted_at', 'desc')->paginate($limit);
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }
    public function countByConditions(array $conditions = []): int
    {
        $query = User::query();

        if (!empty($conditions)) {
            $query = $this->filterUserList($query, $conditions);
        }

        return $query->count();
    }



}