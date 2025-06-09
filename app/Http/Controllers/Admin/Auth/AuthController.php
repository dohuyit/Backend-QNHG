<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest\ForgotPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest\ResetPasswordRequest;
use App\Services\Auth\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    // Gửi mail reset password
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $email = $request->email;
        $result = $this->authService->sendResetPasswordEmail($email);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
    // Xác thực token và đổi mật khẩu
    public function resetPassword(string $id, ResetPasswordRequest $request)
    {
        $token = $request->token;
        $password = $request->password;

        $result = $this->authService->resetPassword($id, $token, $password);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}
