<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginAdminRequest;
use App\Http\Requests\ForgotPasswordRequest\ForgotPasswordRequest;
use App\Http\Requests\ForgotPasswordRequest\ResetPasswordRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

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

    public function login(LoginAdminRequest $request)
    {
        $data = $request->only(['email', 'password']);

        $result = $this->authService->login($data);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(data: $result->getData(), message: $result->getMessage());
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $result = $this->authService->logout($user);

        if (!$result->isSuccessCode()) {
            return $this->responseFail(message: $result->getMessage());
        }

        return $this->responseSuccess(message: $result->getMessage());
    }
}