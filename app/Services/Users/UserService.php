<?php

namespace App\Services\Users;

use App\Common\DataAggregate;
use App\Common\ListAggregate;
use App\Models\User;
use App\Repositories\Users\UserRepositoryInterface;
use Illuminate\Support\Facades\Storage;

class UserService
{
    protected UserRepositoryInterface $userRepository;
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public  function createUser(array $data): DataAggregate
    {
        $result = new DataAggregate();
        $avatarFile = $data['avatar'] ?? null;

       $data = [
            'username'      => $data['username'],
            'password'      => bcrypt($data['password']),
            'full_name'     => $data['full_name'],
            'email'         => $data['email'],
            'phone_number'  => $data['phone_number'],
            'status'        => $data['status'] ?? User::STATUS_ACTIVE,
            'email_verified_at' => $data['email_verified_at'] ?? null,
            'last_login'    => $data['last_login'] ?? null,
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

        $result->setResultSuccess(message: 'Thêm mới người dùng thành công!');
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
            'username'      => $data['username'] ?? $user->username,
            'full_name'     => $data['full_name'] ?? $user->full_name,
            'email'         => $data['email'] ?? $user->email,
            'phone_number'  => $data['phone_number'] ?? $user->phone_number,
            'status'        => $data['status'] ?? $user->status,
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
        $limit = !empty($params['limit']) && $params['limit'] > 0 ? (int)$params['limit'] : 10;

        $pagination = $this->userRepository->getUserList(filter: $filter, limit: $limit);

        $data = [];
        foreach ($pagination->items() as $item) {
            $data[] = [
                'id' => (string)$item->id,
                'username' => $item->username,
                'full_name' => $item->full_name,
                'email' => $item->email,
                'phone_number' => $item->phone_number,
                'status' => $item->status,
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

        if (! $user) {
            $result->setResultError(
                message: 'Dữ liệu không hợp lệ',
                errors: ['user_id' => ['Người dùng không tồn tại.']]
            );
            return $result;
        }

        $updated = $this->userRepository->updateByConditions(
            ['id' => $user->id],
            ['status' => User::STATUS_INACTIVE]
        );

        if (! $updated) {
            $result->setResultError(message: 'Cập nhật trạng thái người dùng thất bại.');
            return $result;
        }

        $result->setResultSuccess(message: 'Người dùng đã được chuyển sang trạng thái ngừng hoạt động!');
        return $result;
    }

    public function blockUser(User $user): DataAggregate
    {
        $result = new DataAggregate();

        $updateData = [
            'status' => User::STATUS_BLOCKED,
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
            message: 'Đã chuyển tài khoản về chế độ bị khoá',
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

}
