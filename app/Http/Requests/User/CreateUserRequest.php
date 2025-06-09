<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class CreateUserRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:6',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive,banned',
        ];
    }
    public function attributes(): array
    {
        return [
            'username' => 'tên đăng nhập',
            'password' => 'mật khẩu',
            'avatar' => 'ảnh đại diện',
            'full_name' => 'họ và tên',
            'email' => 'email',
            'phone_number' => 'số điện thoại',
            'branch_id' => 'chi nhánh',
            'status' => 'trạng thái',
        ];
    }

}
