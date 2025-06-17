<?php

namespace App\Http\Controllers\Admin\PermissionGroup;

use App\Http\Controllers\Controller;
use App\Http\Requests\PermissionGroup\CreatePermissionGroupRequest;
use App\Http\Requests\PermissionGroup\UpdatePermissionGroupRequest;
use App\Repositories\PermissionGroup\PermissionGroupRepositoryInterface;
use App\Services\PermissionGroup\PermissionGroupService;
use Illuminate\Http\Request;
class PermissionGroupController extends Controller
{
    private PermissionGroupService $permissionGroupService;

    protected PermissionGroupRepositoryInterface $permissionGroupRepository;

    public function __construct(PermissionGroupService $permissionGroupService,
                                PermissionGroupRepositoryInterface $permissionGroupRepository)
    {
        $this->permissionGroupService = $permissionGroupService;
        $this->permissionGroupRepository = $permissionGroupRepository;
    }

    public function createPermissionGroup(CreatePermissionGroupRequest $request)
    {
        $data = $request->only(['group_name', 'description']);

        $result = $this->permissionGroupService->createPermissionGroup($data);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function updatePermissionGroup(UpdatePermissionGroupRequest $request, string $id)
    {
        $data = $request->only(['group_name', 'description']);

        $group = $this->permissionGroupRepository->getByConditions(['id' => $id]);

        if (! $group) {
            return $this->responseFail(message: 'Nhóm quyền không tồn tại', statusCode: 404);
        }

        $result = $this->permissionGroupService->updatePermissionGroup($data, $group);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function getPermissionGroupLists(Request $request)
    {
        $params = $request->only('page', 'limit', 'group_name', 'description');

        $result = $this->permissionGroupService->getListPermissionGroups($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function deletePermissionGroup(Request $request, string $id)
    {
        $group = $this->permissionGroupRepository->getByConditions(['id' => $id]);

        if (!$group) {
            return $this->responseFail(message: 'Nhóm quyền không tồn tại.', statusCode: 404);
        }

        $result = $this->permissionGroupService->deleteGroup($group);
        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function restorePermissionGroup(string $id)
    {
        $group = $this->permissionGroupRepository->getByConditions(['id' => $id]);

        if (!$group) {
            return $this->responseFail(message: 'Nhóm quyền không tồn tại.');
        }

        $result = $this->permissionGroupService->restoreGroup($group);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }


}
