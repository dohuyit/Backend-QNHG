<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\Users\UserRepositoryInterface;
use App\Services\Users\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserService $userService;
    protected UserRepositoryInterface $userRepository;
    public function __construct(
        UserService $userService,
        UserRepositoryInterface $userRepository
    ) {
        $this->userService = $userService;
        $this->userRepository = $userRepository;
    }

    public function createUser(CreateUserRequest $request)
    {
        $req = $request->only([
            'username',
            'password',
            'avatar',
            'full_name',
            'email',
            'phone_number',
            'status',
            'role_id'
        ]);

        $result = $this->userService->createUser($req);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
    public function updateUser(UpdateUserRequest $request, int $id)
    {

        $data = $request->only([
            'username',
            'avatar',
            'full_name',
            'email',
            'phone_number',
            'status',
        ]);

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar');
        }

        $result = $this->userService->updateUser($id, $data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }

    public function getListUser(Request $request)
    {
        $params = $request->only(
            'page',
            'limit',
            'perPage',
            'keyword',
            'username',
            'email',
            'full_name',
            'phone_number',
            'status',
        );

        $result = $this->userService->getListUsers($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function deleteUser($id)
    {
        $user = $this->userRepository->getByConditions(['id' => $id]);

        if (!$user) {
            return $this->responseFail(message: 'Người dùng hiện tại không tồn tại.');
        }

        $result = $this->userService->deleteUser($user);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), errors: $result->getErrors());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
    public function blockUser(string $id, Request $request)
    {
        $user = $this->userRepository->getByConditions(['id' => $id]);
        if (!$user) {
            return $this->responseFail(message: 'Tài khoản không tồn tại', statusCode: 404);
        }

        $result = $this->userService->blockUser($user);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }

    public function unblockUser(string $id, Request $request)
    {
        $user = $this->userRepository->getByConditions(['id' => $id]);
        if (!$user) {
            return $this->responseFail(message: 'Tài khoản không tồn tại', statusCode: 404);
        }

        $result = $this->userService->unblockUser($user);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
    public function countByStatus()
    {
        $result = $this->userService->countByStatus();

        return $this->responseSuccess($result);
    }

    public function getUserDetail(string $id)
    {
        $result = $this->userService->getUserDetail($id);

        if (! $result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), statusCode: 404);
        }

        return $this->responseSuccess($result->getData());
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $user = auth()->user();
        if (!$user) {
            return $this->responseFail(message: 'Người dùng chưa được xác thực.');
        }
        $data = $request->only(['old_password', 'new_password']);
        $result = $this->userService->changePassword($user->id, $data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}
