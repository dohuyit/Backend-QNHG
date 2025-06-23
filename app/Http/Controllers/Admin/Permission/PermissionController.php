<?php

namespace App\Http\Controllers\Admin\Permission;

use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\CreatePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Repositories\Permission\PermissionRepositoryInterface;
use App\Services\Permissions\PermissionService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    private PermissionService $permissionService;

    protected PermissionRepositoryInterface $permissionRepository;

    public function __construct(
        PermissionService $permissionService,
        PermissionRepositoryInterface $permissionRepository
    ) {
        $this->permissionService = $permissionService;
        $this->permissionRepository = $permissionRepository;
    }

    public function getPermissionLists(Request $request)
    {
        $params = $request->only('page', 'limit', 'perPage', 'keyword', 'permission_name', 'description', 'permission_group_id');

        $result = $this->permissionService->getListPermissions($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function createPermission(CreatePermissionRequest $request)
    {
        $data = $request->only(['permission_name', 'description', 'permission_group_id']);

        $result = $this->permissionService->createPermission($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function updatePermission(UpdatePermissionRequest $request, string $id)
    {
        $data = $request->only(['permission_name', 'description', 'permission_group_id']);

        $permission = $this->permissionRepository->getByConditions(['id' => $id]);

        if (!$permission) {
            return $this->responseFail(message: 'Quyền hạn không tồn tại', statusCode: 404);
        }

        $result = $this->permissionService->updatePermission($data, $permission);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function deletePermission(string $id)
    {
        $permission = $this->permissionRepository->getByConditions(['id' => $id]);

        if (!$permission) {
            return $this->responseFail(message: 'Quyền không tồn tại', statusCode: 404);
        }

        $result = $this->permissionService->deletePermission($permission);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(
                message: $result->getMessage(),
                errors: $result->getErrors()
            );
        }


        return $this->responseSuccess(message: $result->getMessage());
    }

}