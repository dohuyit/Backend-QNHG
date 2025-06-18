<?php

namespace App\Http\Controllers\Admin\Role;

use App\Http\Controllers\Controller;
use App\Http\Requests\Role\CreateRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Repositories\Role\RoleRepositoryInterface;
use App\Services\Role\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private RoleService $roleService;

    protected RoleRepositoryInterface $roleRepository;

    public function __construct(
        RoleService $roleService,
        RoleRepositoryInterface $roleRepository
    ) {
        $this->roleService = $roleService;
        $this->roleRepository = $roleRepository;

    }

    public function createRole(CreateRoleRequest $request)
    {
        $data = $request->only(['role_name', 'description']);
        $result = $this->roleService->createRole($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function updateRole(UpdateRoleRequest $request, string $id)
    {
        $data = $request->only(['role_name', 'description']);

        $role = $this->roleRepository->getByConditions(['id' => $id]);

        if (!$role) {
            return $this->responseFail(message: 'Vai trò không tồn tại', statusCode: 404);
        }

        $result = $this->roleService->updateRole($data, $role);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function getListRoles(Request $request)
    {
        $params = $request->only(
            'keyword',
            'perPage',
            'page',
            'limit',
            'role_name',
            'description',
        );

        $result = $this->roleService->getListRoles($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function deleteRole(Request $request, string $id)
    {
        $role = $this->roleRepository->getByConditions(['id' => $id]);

        if (!$role) {
            return $this->responseFail(message: 'Vai trò không tồn tại.', statusCode: 404);
        }

        $result = $this->roleService->deleteRole($role);
        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}