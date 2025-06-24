<?php

namespace App\Services\PermissionGroup;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\PermissionGroup;
use App\Repositories\PermissionGroup\PermissionGroupRepositoryInterface;

class PermissionGroupService
{
    protected PermissionGroupRepositoryInterface $permissionGroupRepository;

    public function __construct(PermissionGroupRepositoryInterface $permissionGroupRepository)
    {
        $this->permissionGroupRepository = $permissionGroupRepository;
    }

    public function createPermissionGroup(array $data): DataAggregate
    {
        $result = new DataAggregate;

        $createData = [
            'group_name' => $data['group_name'],
            'description' => $data['description'] ?? null,
        ];

        $ok = $this->permissionGroupRepository->createData($createData);

        if (!$ok) {
            $result->setMessage('Thêm nhóm quyền thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Thêm nhóm quyền thành công!');
        return $result;
    }

    public function updatePermissionGroup(array $data, PermissionGroup $group): DataAggregate
    {
        $result = new DataAggregate;

        $updateData = [
            'group_name' => $data['group_name'],
            'description' => $data['description'] ?? null,
        ];

        $ok = $this->permissionGroupRepository->updateByConditions(['id' => $group->id], $updateData);

        if (!$ok) {
            $result->setMessage('Cập nhật nhóm quyền thất bại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật nhóm quyền thành công!');
        return $result;
    }

    public function getListPermissionGroups(array $params): ListAggregate
    {
        $filter = $params;
        $limit = (int) ($params['perPage'] ?? $params['limit'] ?? 10);

        $pagination = $this->permissionGroupRepository->getPermissionGroupList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'group_name' => $item->group_name ?? null,
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

    public function deleteGroup(PermissionGroup $group): DataAggregate
    {
        $result = new DataAggregate();

        $used = $this->permissionGroupRepository->isUsedInPermissions($group->id);
        if ($used) {
            $result->setResultError(
                'Không thể xóa nhóm quyền vì đang được sử dụng.',
                ['group_id' => ['Nhóm quyền đang được sử dụng.']]
            );
            return $result;
        }

        $deleted = $this->permissionGroupRepository->forceDelete($group);

        if (!$deleted) {
            $result->setResultError(message: 'Xóa nhóm quyền thất bại. Vui lòng thử lại.');
            return $result;
        }

        $result->setResultSuccess(message: 'Xóa nhóm quyền thành công!');
        return $result;
    }
}