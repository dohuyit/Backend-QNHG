<?php

namespace App\Services\RolePermission;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\RolePermission;
use App\Repositories\RolePermission\RolePermissionRepositoryInterface;

class RolePermissionService
{
    protected RolePermissionRepositoryInterface $rolePermissionRepository;

    public function __construct(RolePermissionRepositoryInterface $rolePermissionRepository)
    {
        $this->rolePermissionRepository = $rolePermissionRepository;
    }

    public function getListRolePermissions(array $params): ListAggregate
    {
        $filter = $params;
        $limit = (int) ($params['perPage'] ?? $params['limit'] ?? 10);


        $pagination = $this->rolePermissionRepository->getRolePermissionList($filter, $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'role' => [
                    'role_id' => $item->role_id,
                    'role_name' => $item->role->role_name ?? null,
                    'description' => $item->role->description ?? null,
                ],
                'permission' => [
                    'permission_id' => $item->permission_id,
                    'permission_name' => $item->permission->permission_name ?? null,
                    'description' => $item->permission->description ?? null,
                ],
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

    public function createRolePermission(array $data): DataAggregate
    {
        $result = new DataAggregate;

        $exists = $this->rolePermissionRepository->exists([
            'role_id' => $data['role_id'],
            'permission_id' => $data['permission_id'],
        ]);

        if ($exists) {
            $result->setMessage('Quyền đã được gán cho vai trò này!');
            return $result;
        }

        $ok = $this->rolePermissionRepository->createData($data);

        if (!$ok) {
            $result->setMessage('Gán quyền cho vai trò thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Gán quyền cho vai trò thành công!');
        return $result;
    }

    public function updateRolePermission(array $data, RolePermission $rolePermission): DataAggregate
    {
        $result = new DataAggregate;

        $updateData = [
            'role_id' => $data['role_id'],
            'permission_id' => $data['permission_id'],
        ];

        $ok = $this->rolePermissionRepository->updateByConditions(['id' => $rolePermission->id], $updateData);

        if (!$ok) {
            $result->setMessage('Cập nhật phân quyền vai trò thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật phân quyền vai trò thành công!');
        return $result;
    }

    public function deleteRolePermission(RolePermission $rolePermission): DataAggregate
    {
        $result = new DataAggregate();

        $deleted = $this->rolePermissionRepository->delete($rolePermission);

        if (!$deleted) {
            $result->setMessage('Xóa phân quyền vai trò thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa phân quyền vai trò thành công!');
        return $result;
    }

}