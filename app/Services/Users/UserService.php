<?php

namespace App\Services\Users;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\User;
use App\Repositories\UserRole\UserRoleRepositoryInterface;
use App\Repositories\Users\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    protected UserRoleRepositoryInterface $userRoleRepository;
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserRoleRepositoryInterface $userRoleRepository
    ) {
        $this->userRepository = $userRepository;
        $this->userRoleRepository = $userRoleRepository;
    }

    public function createUser(array $data): DataAggregate
    {
        $result = new DataAggregate();
        $avatarFile = $data['avatar'] ?? null;

        $roleId = $data['role_id'] ?? null;

        $plainPassword = $data['password'];
        $hashedPassword = bcrypt($plainPassword);
        $data = [
            'username' => $data['username'],
            'full_name' => $data['full_name'],
            'password' => $hashedPassword,
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'status' => $data['status'] ?? User::STATUS_INACTIVE,
            'email_verified_at' => $data['email_verified_at'] ?? null,
            'last_login' => $data['last_login'] ?? null,
        ];
        if ($avatarFile && $avatarFile->isValid()) {
            $extension = $avatarFile->getClientOriginalExtension();
            $filename = 'user_' . uniqid() . '.' . $extension;
            $path = $avatarFile->storeAs('users', $filename, 'public');
            $data['avatar'] = $path;
        }

        $ok = $this->userRepository->createData($data);

        if (!$ok) {
            $result->setMessage('Thêm mới người dùng thất bại, vui lòng thử lại!');
            return $result;
        }
        $user = $this->userRepository->getByConditions(['username' => $data['username']]);
        if (!$user) {
            $result->setMessage('Không tìm thấy người dùng sau khi tạo!');
            return $result;
        }

        if (!empty($roleId)) {
            $this->userRoleRepository->createData([
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);
        }
        try {
            $user->activation_token = Str::random(64);
            $user->save();
            $activationLink = route('admin.activate', ['token' => $user->activation_token]);


            Mail::to($user->email)->send(new \App\Mail\ActivateUserMail($user, $activationLink, $plainPassword));
        } catch (\Exception $e) {

            Log::error('Send activation email failed: ' . $e->getMessage());
        }

        $result->setResultSuccess(message: 'Thêm mới người dùng thành công! Đã gửi email kích hoạt.');
        return $result;
    }
    public function updateUser(int $id, array $data): DataAggregate
    {
        $result = new DataAggregate();

        $user = $this->userRepository->getByConditions(['id' => $id]);
        if (!$user) {
            $result->setMessage('Người dùng không tồn tại');
            return $result;
        }

        $updateData = [
            'username' => $data['username'] ?? $user->username,
            'full_name' => $data['full_name'] ?? $user->full_name,
            'email' => $data['email'] ?? $user->email,
            'phone_number' => $data['phone_number'] ?? $user->phone_number,
            'status' => $data['status'] ?? $user->status,
        ];


        if (!empty($data['avatar']) && $data['avatar']->isValid()) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $file = $data['avatar'];
            $extension = $file->getClientOriginalExtension();
            $filename = 'user_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('users', $filename, 'public');
            $updateData['avatar'] = $path;
        }

        $ok = $this->userRepository->updateByConditions(['id' => $id], $updateData);
        if (!$ok) {
            $result->setMessage('Cập nhật người dùng thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(message: 'Cập nhật người dùng thành công!');
        return $result;
    }

    public function getListUsers(array $params): ListAggregate
    {
        $filter = $params;
        $limit = (int) ($params['perPage'] ?? $params['limit'] ?? 10);

        $pagination = $this->userRepository->getUserList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string) $item->id,
                'username' => $item->username,
                'full_name' => $item->full_name,
                'email' => $item->email,
                'phone_number' => $item->phone_number,
                'status' => $item->status,
                'avatar' => $item->avatar,
                'last_login' => $item->last_login,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
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

    public function deleteUser(User $user): DataAggregate
    {
        $result = new DataAggregate();

        if (!$user) {
            $result->setResultError(
                message: 'Dữ liệu không hợp lệ',
                errors: ['user_id' => ['Người dùng không tồn tại.']]
            );
            return $result;
        }

        // Không cho xóa tài khoản đang đăng nhập
        if ($user->id === Auth::id()) {
            $result->setResultError(
                message: 'Không thể xóa chính tài khoản đang đăng nhập.',
                errors: ['user' => ['Không thể xóa tài khoản đang đăng nhâp.']]
            );
            return $result;
        }

        // Không cho xóa tài khoản có vai trò admin
        if ($user->roles()->pluck('role_name')->contains('admin')) {
            $result->setResultError(message: 'Không thể xóa tài khoản có vai trò Admin.', errors: ['user' => ['Tài khoản này có quyền admin, không được phép xóa.']]);
            return $result;
        }

        // Xóa hẳn khỏi DB (hard delete)
        try {
            $user->delete();
        } catch (\Throwable $e) {
            $result->setResultError(message: 'Xóa người dùng thất bại: ' . $e->getMessage());
            return $result;
        }

        $result->setResultSuccess(message: 'Người dùng đã được xóa khỏi hệ thống!');
        return $result;
    }



    public function blockUser(User $user): DataAggregate
    {
        $result = new DataAggregate();

        // Không cho phép khoá admin
        if ($user->role === User::ROLE_ADMIN) {
            $result->setMessage('Không thể khoá tài khoản admin');
            return $result; // << PHẢI return $result
        }

        // Không cho phép khoá tài khoản đang đăng nhập
        if (auth()->id() === $user->id) {
            $result->setMessage('Không thể khoá chính tài khoản đang đăng nhập');
            return $result; // << PHẢI return $result
        }

        $updateData = [
            'status' => User::STATUS_INACTIVE,
            'updated_at' => now(),
        ];

        $updated = $this->userRepository->updateByConditions(
            conditions: ['id' => $user->id],
            updateData: $updateData
        );

        if (!$updated) {
            $result->setMessage('Khoá tài khoản thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(
            message: 'Tài khoản đã được chuyển sang trạng thái ngừng hoạt động',
            data: []
        );

        return $result;
    }


    public function unblockUser(User $user): DataAggregate
    {
        $result = new DataAggregate();

        $updateData = [
            'status' => User::STATUS_ACTIVE,
            'updated_at' => now(),
        ];

        $updated = $this->userRepository->updateByConditions(
            conditions: ['id' => $user->id],
            updateData: $updateData
        );

        if (!$updated) {
            $result->setMessage('Mở khoá tài khoản thất bại, vui lòng thử lại!');
            return $result;
        }

        $result->setResultSuccess(
            message: 'Đã mở khóa tài khoản về hoạt động',
            data: []
        );
        return $result;
    }
    public function countByStatus(): array
    {
        $listStatus = ['active', 'inactive', 'pending_activation','blocked'];
        $counts = [];

        foreach($listStatus as $status) {
            $counts[$status] = $this->userRepository->countByConditions(['status' => $status]);
        }
        return $counts;
    }

    public function getUserDetail(string $id): DataAggregate
    {
        $result = new DataAggregate;

        $user = $this->userRepository->getByConditions(['id' => $id]);

        if (! $user) {
            $result->setResultError(message: 'Người dùng không tồn tại');
            return $result;
        }

        $user->load('roles.permissions');

        $user->makeHidden([
            'created_at',
            'updated_at',
            'deleted_at',
            'email_verified_at',
            'remember_token',
            'roles',
        ]);

        $data = [
            'user' => $user,
            'role' => [
                'id' => $user->roles()->first()?->id,
                'role_name' => $user->getPrimaryRoleName(),
            ],
            'permissions' => $user->getAllPermissions(),
        ];

        $result->setResultSuccess(data: $data);
        return $result;
    }

    public function changePassword(string $userId, array $data): DataAggregate
    {
        $result = new DataAggregate();

        $user = $this->userRepository->getByConditions(['id' => $userId]);
        if (!$user) {
            $result->setMessage('Người dùng không tồn tại.');
            return $result;
        }

        if (!Hash::check($data['old_password'], $user->password)) {
            $result->setMessage('Mật khẩu cũ không đúng.');
            return $result;
        }

        $newHashedPassword = bcrypt($data['new_password']);
        $updated = $this->userRepository->updateByConditions(
            ['id' => $userId],
            ['password' => bcrypt($data['new_password'])]
        );

        if (!$updated) {
            $result->setMessage('Đổi mật khẩu thất bại. Vui lòng thử lại.');
            return $result;
        }

        $result->setResultSuccess(message: 'Đổi mật khẩu thành công!');
        return $result;
    }
}
