<?php

namespace App\Services\Auth;

use App\Common\DataAggregate;
use App\Repositories\Auth\AuthClientRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class AuthClientService
{
    protected AuthClientRepositoryInterface $authClientRepository;
    public function __construct(AuthClientRepositoryInterface $authClientRepository)
    {
        $this->authClientRepository = $authClientRepository;
    }

    public function login(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $user  = $this->authClientRepository->getByConditions(['email'  =>  $data['email']]);
        
        if (!$user ||  !Hash::check($data['password'], $user->password)){
            $result->setResultError( 'Email hoặc mật khẩu không chính xác');
            return  $result;
        }

        $token = $user->createToken('client-token')->plainTextToken;
        $result->setResultSuccess([
            'user' => $user,
            'token' => $token,
        ]);

        $result->setMessage(message: 'Đăng nhập thành công');

        return $result;
    }
    public function register(array $data): DataAggregate
    {
        $result = new DataAggregate();

        $data['password'] = bcrypt($data['password']);
        $data['role'] = 'client';

        $user = $this->authClientRepository->createData($data);
        $token = $user->createToken('client-token')->plainTextToken;

        $result->setResultSuccess([
            'user' => $user,
            'token' => $token,
        ]);

        $result->setMessage(message: 'Đăng ký thành công');

        return $result;
    }
    public function logout($user):  DataAggregate
    {
        $result = new DataAggregate();

        $user->currentAccessToken()->delete();
        $result->setResultSuccess(message: 'Đăng xuất thành công');
        
        return $result;
    }
}