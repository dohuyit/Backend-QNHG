<?php
namespace App\Http\Requests\ForgotPasswordRequest;

use App\Http\Requests\BaseFormRequest;

class ForgotPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|exists:users,email',
        ];
    }

    public function attributes(): array
    {
        return [
            'email' => 'địa chỉ email',
        ];
    }

}
