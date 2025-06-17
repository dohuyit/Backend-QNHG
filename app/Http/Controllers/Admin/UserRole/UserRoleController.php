<?php

namespace App\Http\Controllers\Admin\UserRole;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRole\CreateUserRoleRequest;
use App\Http\Requests\UserRole\UpdateUserRoleRequest;
use App\Repositories\UserRole\UserRoleRepositoryInterface;
use App\Services\UserRole\UserRoleService;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    private UserRoleService $userRoleService;

    protected UserRoleRepositoryInterface $userRoleRepository;

    public function __construct(UserRoleRepositoryInterface $userRoleRepository,
                                UserRoleService $userRoleService)
    {
        $this->userRoleRepository = $userRoleRepository;
        $this->userRoleService = $userRoleService;
    }

    public function getUserRoleLists(Request $request)
    {
        $params = $request->only('page', 'limit', 'user_id', 'role_id');

        $result = $this->userRoleService->getListUserRoles($params);
        $data = $result->getResult();

        return $this->responseSuccess($data);
    }

    public function createUserRole(CreateUserRoleRequest $request)
    {
        $data = $request->only(['user_id', 'role_id']);

        $result = $this->userRoleService->createUserRole($data);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function updateUserRole(UpdateUserRoleRequest $request, string $id)
    {
        $data = $request->only(['user_id', 'role_id']);

        $userRole = $this->userRoleRepository->getByConditions(['id' => $id]);

        if (! $userRole) {
            return $this->responseFail(message: 'Phân quyền người dùng không tồn tại', statusCode: 404);
        }

        $result = $this->userRoleService->updateUserRole($data, $userRole);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function deleteUserRole(string $id)
    {
        $userRole = $this->userRoleRepository->getByConditions(['id' => $id]);

        if (! $userRole) {
            return $this->responseFail(message: 'Liên kết người dùng - vai trò không tồn tại.', statusCode: 404);
        }

        $result = $this->userRoleService->deleteUserRole($userRole);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

}
