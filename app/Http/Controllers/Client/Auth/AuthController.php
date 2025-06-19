<?php
namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginClientRequest;
use App\Http\Requests\Auth\RegisterClientRequest;
use App\Services\Auth\AuthClientService;

class AuthController extends Controller
{
    protected AuthClientService $authClientService;
    public function __construct(AuthClientService $authClientService)
    {
        $this->authClientService = $authClientService;
    }
    public function login(LoginClientRequest $request)
    {
        $data  = $request->only('email', 'password');
        $result = $this->authClientService->login($data);
        if (!$result->isSuccessCode()){
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(
            data: $result->getData(),
            message: $result->getMessage()
        );
    }
    public function register(RegisterClientRequest $request)
    {
        $data  = $request->only('full_name','username', 'email', 'password');
        $result = $this->authClientService->register($data);
        if (!$result->isSuccessCode()){
            return $this->responseFail(message: $result->getMessage());
        }
        return $this->responseSuccess(message: $result->getMessage());
    }
    public function logout()
    {
        $user = auth()->user();
        $result = $this->authClientService->logout($user);
        return $this->responseSuccess(message: $result->getMessage());
    }

}