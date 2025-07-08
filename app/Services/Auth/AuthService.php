<?php

namespace App\Services\Auth;

use App\Common\DataAggregate;
use App\Models\User;
use App\Repositories\Auth\AuthVerifyTokenRepositoryInterface;
use App\Repositories\Users\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    protected AuthVerifyTokenRepositoryInterface $authVerifyTokenRepository;
    protected UserRepositoryInterface $userRepository;
    public function __construct(
        AuthVerifyTokenRepositoryInterface $authVerifyTokenRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->authVerifyTokenRepository = $authVerifyTokenRepository;
        $this->userRepository = $userRepository;
    }
    public function sendResetPasswordEmail(string $email): DataAggregate
    {
        $result = new DataAggregate();

        $user = $this->userRepository->getByConditions(['email' => $email]);
        if (!$user) {
            $result->setMessage('Email không tồn tại trong hệ thống!');
            return $result;
        }

        $token = Str::random(64);
        $expiredAt = Carbon::now()->addMinutes(60);

        $this->authVerifyTokenRepository->createData([
            'user_id' => $user->id,
            'token' => $token,
            'description' => 'Reset mật khẩu',
            'status' => 'active',
            'expired_at' => $expiredAt,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        $link = env('FRONTEND_URL', 'http://localhost:5173') . "/admin/reset-password/{$user->id}?token={$token}";

        $content = "<h1>Yêu cầu đặt lại mật khẩu</h1>
                   <p>Chào bạn {$user->full_name},</p>
                   <p>Bạn đã yêu cầu đặt lại mật khẩu. Vui lòng nhấn vào liên kết bên dưới để đặt lại mật khẩu:</p>
                   <p><a href='{$link}'>Đặt lại mật khẩu</a></p>
                   <p>Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>";

        Mail::html($content, function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Yêu cầu đặt lại mật khẩu');
        });


        $result->setResultSuccess(['message' => 'Gửi email đặt lại mật khẩu thành công, vui lòng kiểm tra hộp thư.']);

        return $result;
    }

    public function resetPassword(string $id, string $token, string $password): DataAggregate
    {
        $result = new DataAggregate();

        $user = $this->userRepository->getByConditions(['id' => $id]);
        if (!$user) {
            $result->setMessage('Người dùng không tồn tại!');
            return $result;
        }

        $verifyToken = $this->authVerifyTokenRepository->getByConditions([
            'token' => $token,
            'user_id' => $id,
            'status' => 'active',
        ]);

        if (!$verifyToken) {
            $result->setMessage('Token không hợp lệ hoặc đã hết hạn!');
            return $result;
        }

        if ($verifyToken->expired_at < Carbon::now()) {
            $result->setMessage('Token đã hết hạn!');
            return $result;
        }

        $this->userRepository->updateByConditions(
            ['id' => $id],
            [
                'password' => Hash::make($password),
                'email_verified_at' => Carbon::now(),
            ]
        );


        $this->authVerifyTokenRepository->updateData($verifyToken->id, [
            'status' => 'inactive',
            'updated_at' => Carbon::now(),
        ]);

        $result->setResultSuccess(['message' => 'Cập nhật thành công']);

        return $result;
    }

    public function login(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $user = User::with('roles.permissions')->where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            $result->setMessage('Email hoặc mật khẩu không đúng.');
            return $result;
        }

        if ($user->status !== User::STATUS_ACTIVE) {
            $result->setMessage('Tài khoản chưa được kích hoạt.');
            return $result;
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        $permissions = $user->roles
            ->flatMap(fn($role) => $role->permissions)
            ->pluck('permission_name')
            ->unique()
            ->values();

        $roles = $user->roles->map(fn($role) => [
            'id' => $role->id,
            'name' => $role->role_name,
        ]);

        $result->setResultSuccess(
            data: [
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'full_name' => $user->full_name,
                    'roles' => $roles,
                    'permissions' => $permissions,
                ],
                'token' => $token, // gửi token về FE
            ],
            message: 'Đăng nhập thành công!'
        );

        return $result;
    }

    public function logout(User $user): DataAggregate
    {
        $result = new DataAggregate();

        try {
            $user->tokens()->delete();
            $result->setResultSuccess(message: 'Đăng xuất thành công!');
        } catch (\Exception $e) {
            $result->setMessage('Đăng xuất thất bại: ' . $e->getMessage());
        }

        return $result;
    }
}
