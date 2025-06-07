<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseFormRequest;

class UpdateUserRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('id');

        return [
            'username' => 'required|string|max:50|unique:users,username,' . $userId,
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone_number' => 'nullable|string|max:20',
            'branch_id' => 'nullable|exists:branches,id',
            'status' => 'required|in:active,inactive,banned',
        ];
    }

    public function attributes(): array
    {
        return [
            'username' => 'tên đăng nhập',
            'avatar' => 'ảnh đại diện',
            'full_name' => 'họ và tên',
            'email' => 'email',
            'phone_number' => 'số điện thoại',
            'branch_id' => 'chi nhánh',
            'status' => 'trạng thái',
        ];
    }
}
