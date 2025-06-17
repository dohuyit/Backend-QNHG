<?php

namespace App\Services\Permissions;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\Permission;
use App\Repositories\Permission\PermissionRepositoryInterface;

class PermissionService
{
    protected PermissionRepositoryInterface $permissionRepository;

    public function __construct(PermissionRepositoryInterface $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function getListPermissions(array $params): ListAggregate
    {
        $filter = $params;
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int) $params['limit'] : 10;

        $pagination = $this->permissionRepository->getPermissionList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'permission_name' => $item->permission_name ?? null,
                'description' => $item->description ?? null,
                'permission_group_id' => $item->permission_group_id ?? null,
                'permission_group_name' => $item->permission_group_name ?? null,
                'permission_group_description' => $item->permission_group_description ?? null,
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


    public function createPermission(array $data): DataAggregate
    {
        $result = new DataAggregate;

        $createData = [
            'permission_name' => $data['permission_name'],
            'description' => $data['description'] ?? null,
            'permission_group_id' => $data['permission_group_id'],
        ];

        $ok = $this->permissionRepository->createData($createData);

        if (! $ok) {
            $result->setMessage('Thêm quyền thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Thêm quyền thành công!');
        return $result;
    }

    public function updatePermission(array $data, Permission $permission): DataAggregate
    {
        $result = new DataAggregate;

        $updateData = [
            'permission_name' => $data['permission_name'],
            'description' => $data['description'] ?? null,
            'permission_group_id' => $data['permission_group_id'],
        ];

        $ok = $this->permissionRepository->updateByConditions(['id' => $permission->id], $updateData);

        if (! $ok) {
            $result->setMessage('Cập nhật quyền hạn thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật quyền hạn thành công!');
        return $result;
    }

    public function deletePermission(Permission $permission): DataAggregate
    {
        $result = new DataAggregate;

        $ok = $this->permissionRepository->delete($permission);
        if (! $ok) {
            $result->setMessage('Xóa quyền hạn thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa quyền hạn thành công!');
        return $result;
    }

    public function restorePermission(Permission $permission): DataAggregate
    {
        $result = new DataAggregate;

        $ok = $this->permissionRepository->restore($permission);
        if (! $ok) {
            $result->setMessage('Khôi phục quyền hạn thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Khôi phục quyền hạn thành công!');
        return $result;
    }
}
