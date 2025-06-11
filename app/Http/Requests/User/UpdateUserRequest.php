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
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone_number' => 'nullable|string|max:20|unique:users,phone_number,' . $userId,
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
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập :attribute.',
            'username.string' => ':attribute phải là chuỗi ký tự.',
            'username.max' => ':attribute không được vượt quá :max ký tự.',
            'username.unique' => ':attribute đã tồn tại.',

            'password.string' => ':attribute phải là chuỗi.',
            'password.min' => ':attribute phải có ít nhất :min ký tự.',

            'avatar.image' => ':attribute phải là một hình ảnh.',
            'avatar.mimes' => ':attribute phải có định dạng: :values.',
            'avatar.max' => ':attribute không được vượt quá :max KB.',

            'full_name.required' => 'Vui lòng nhập :attribute.',
            'full_name.string' => ':attribute phải là chuỗi.',
            'full_name.max' => ':attribute không được vượt quá :max ký tự.',

            'email.required' => 'Vui lòng nhập :attribute.',
            'email.email' => ':attribute không đúng định dạng.',
            'email.unique' => ':attribute đã được sử dụng.',

            'phone_number.string' => ':attribute phải là chuỗi ký tự.',
            'phone_number.max' => ':attribute không được vượt quá :max ký tự.',
            'phone_number.unique' => ':attribute đã được sử dụng.',
        ];
    }
}
