<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use App\Repositories\Users\UserRepositoryInterface;
use App\Services\Users\UserService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private UserService $userService;
    protected UserRepositoryInterface  $userRepository;
    public function __construct(UserService $userService,
                                UserRepositoryInterface $userRepository)
    {
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
            'branch_id',
            'status',
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
            'branch_id',
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
            'query',
            'username',
            'email',
            'full_name',
            'phone_number',
            'status',
            'branch_id'
        );

        $result = $this->userService->getListUsers($params);
        $data = $result->getResult();
        return $this->responseSuccess($data);
    }
    public function deleteUser($id)
    {
        $currentUser = User::find($id);

        if (!$currentUser) {
            return $this->responseFail(message: 'Người dùng hiện tại không tồn tại.');
        }

        $result = $this->userService->deleteUser($id, $currentUser);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage(), errors: $result->getErrors());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
    public function blockUser(string $id,Request $request)
    {
        $user = $this->userRepository->getByConditions(['id' => $id]);
        if (!$user) {
            return $this->responseFail(message: 'Tài khoản không tồn tại', statusCode: 404);
        }

        // Gọi service để thực hiện block
        $result = $this->userService->blockUser($user);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
    public function unblockUser(string $id,Request $request)
    {
        $user = $this->userRepository->getByConditions(['id' => $id]);
        if (!$user) {
            return $this->responseFail(message: 'Tài khoản không tồn tại', statusCode: 404);
        }

        // Gọi service để mở khoá
        $result = $this->userService->unblockUser($user);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}
