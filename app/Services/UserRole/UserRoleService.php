<?php

namespace App\Services\UserRole;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\UserRole;
use App\Repositories\UserRole\UserRoleRepositoryInterface;

class UserRoleService
{
    protected UserRoleRepositoryInterface $userRoleRepository;

    public function __construct(UserRoleRepositoryInterface $userRoleRepository)
    {
        $this->userRoleRepository = $userRoleRepository;
    }

    public function getListUserRoles(array $params): ListAggregate
    {
        $filter = $params;
        $limit = (int) ($params['perPage'] ?? $params['limit'] ?? 10);


        $pagination = $this->userRoleRepository->getUserRoleList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'user_id' => $item->user_id,
                'role_id' => $item->role_id,
                'username' => $item->user->username ?? null,
                'email' => $item->user->email ?? null,
                'status' => $item->user->status ?? null,
                'phone_number' => $item->user->phone_number ?? null,
                'role_name' => $item->role->role_name ?? null,
                'role_description' => $item->role->description ?? null,
                'created_at' => $item->created_at?->toDateTimeString(),
                'updated_at' => $item->updated_at?->toDateTimeString(),
            ];
        }

        $result = new ListAggregate($data);
        $result->setMeta(
            page: $pagination->currentPage(),
            perPage: $pagination->perPage(),
            total: $pagination->total()
        );

        return $result;
    }

    public function createUserRole(array $data): DataAggregate
    {
        $result = new DataAggregate;

        $exists = $this->userRoleRepository->existsUserRole($data['user_id'], $data['role_id']);
        if ($exists) {
            $result->setResultError(
                message: 'FAILED',
                errors: ['user_id' => ['Người dùng đã được gán vai trò này.']]
            );

            return $result;
        }
        $createData = [
            'user_id' => $data['user_id'],
            'role_id' => $data['role_id'],
        ];

        $ok = $this->userRoleRepository->createData($createData);

        if (!$ok) {
            $result->setMessage('Gán vai trò cho người dùng thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Gán vai trò cho người dùng thành công!');
        return $result;
    }

    public function updateUserRole(array $data, UserRole $userRole): DataAggregate
    {
        $result = new DataAggregate;

        $exists = $this->userRoleRepository->isDuplicateUserRoleExceptId(
            $data['user_id'],
            $data['role_id'],
            $userRole->id
        );
        if ($exists) {
            $result->setResultError(
                message: 'FAILED',
                errors: ['user_id' => ['Người dùng đã được gán vai trò này.']]
            );
            return $result;
        }

        $updateData = [
            'user_id' => $data['user_id'],
            'role_id' => $data['role_id'],
        ];

        $ok = $this->userRoleRepository->updateByConditions(['id' => $userRole->id], $updateData);

        if (!$ok) {
            $result->setMessage('Cập nhật nhóm người dùng thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật nhóm người dùng thành công!');
        return $result;
    }

    public function deleteUserRole(UserRole $userRole): DataAggregate
    {
        $result = new DataAggregate;

        $ok = $this->userRoleRepository->delete($userRole);

        if (!$ok) {
            $result->setMessage('Xóa thất bại.');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa thành công!');
        return $result;
    }

}