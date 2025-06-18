<?php

namespace App\Http\Controllers\Admin\RolePermission;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolePermission\CreateRolePermissionRequest;
use App\Http\Requests\RolePermission\UpdateRolePermissionRequest;
use App\Repositories\RolePermission\RolePermissionRepositoryInterface;
use App\Services\RolePermission\RolePermissionService;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    private RolePermissionService $rolePermissionService;

    protected RolePermissionRepositoryInterface $rolePermissionRepository;

    public function __construct(RolePermissionService $rolePermissionService,
                                RolePermissionRepositoryInterface $rolePermissionRepository)
    {
        $this->rolePermissionService = $rolePermissionService;
        $this->rolePermissionRepository = $rolePermissionRepository;
    }

    public function getRolePermissionList(Request $request)
    {
        $params = $request->only('page', 'limit', 'role_id', 'permission_id');

        $result = $this->rolePermissionService->getListRolePermissions($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function createRolePermission(CreateRolePermissionRequest $request)
    {
        $data = $request->only(['role_id', 'permission_id']);

        $result = $this->rolePermissionService->createRolePermission($data);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function updateRolePermission(UpdateRolePermissionRequest $request, string $id)
    {
        $data = $request->only(['role_id', 'permission_id']);

        $rolePermission = $this->rolePermissionRepository->getByConditions(['id' => $id]);

        if (! $rolePermission) {
            return $this->responseFail(message: 'Phân quyền vai trò không tồn tại', statusCode: 404);
        }

        $result = $this->rolePermissionService->updateRolePermission($data, $rolePermission);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function deleteRolePermission(string $id)
    {
        $rolePermission = $this->rolePermissionRepository->getByConditions(['id' => $id]);

        if (! $rolePermission) {
            return $this->responseFail(message: 'Liên kết vai trò - quyền không tồn tại', statusCode: 404);
        }

        $result = $this->rolePermissionService->deleteRolePermission($rolePermission);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

}
