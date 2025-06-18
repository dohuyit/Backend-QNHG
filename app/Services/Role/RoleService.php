<?php

namespace App\Services\Role;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\Role;
use App\Repositories\Role\RoleRepositoryInterface;
class RoleService
{
    protected RoleRepositoryInterface $roleRepository;

    public function __construct(RoleRepositoryInterface $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function createRole(array $data): DataAggregate
    {
        $result = new DataAggregate;

        $createData = [
            'role_name' => $data['role_name'],
            'description' => $data['description'] ?? null,
        ];

        $ok = $this->roleRepository->createData($createData);
        if (!$ok) {
            $result->setMessage('Thêm vai trò thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Thêm vai trò thành công!');
        return $result;
    }

    public function updateRole(array $data, Role $role): DataAggregate
    {
        $result = new DataAggregate;

        $updateData = [
            'role_name' => $data['role_name'],
            'description' => $data['description'] ?? null,
        ];

        $ok = $this->roleRepository->updateByConditions(['id' => $role->id], $updateData);

        if (!$ok) {
            $result->setMessage('Cập nhật vai trò thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật vai trò thành công!');
        return $result;
    }

    public function getListRoles(array $params): ListAggregate
    {
        $filter = $params;
        $limit = (int) ($params['perPage'] ?? $params['limit'] ?? 10);

        $pagination = $this->roleRepository->getRoleList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'role_name' => $item->role_name ?? null,
                'description' => $item->description ?? null,
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

    public function deleteRole(Role $role): DataAggregate
    {
        $result = new DataAggregate();

        if (
            $this->roleRepository->isUsedInUserRoles($role->id) ||
            $this->roleRepository->isUsedInRolePermissions($role->id)
        ) {
            $result->setResultError(message: 'Không thể xóa vai trò vì đang được sử dụng.');
            return $result;
        }

        $ok = $this->roleRepository->delete($role);

        if (!$ok) {
            $result->setResultError(message: 'Xóa vai trò thất bại. Vui lòng thử lại.');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa vai trò thành công!');
        return $result;
    }

}