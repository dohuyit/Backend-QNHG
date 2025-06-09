<?php
namespace App\Http\Requests\ForgotPasswordRequest;

use App\Http\Requests\BaseFormRequest;

class ResetPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function attributes(): array
    {
        return [
            'token' => 'mã xác thực',
            'password' => 'mật khẩu mới',
            'password_confirmation' => 'xác nhận mật khẩu',
        ];
    }

}
