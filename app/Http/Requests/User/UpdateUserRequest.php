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
        $userId = $this->route('id') ?? $this->input('id');

        return [
            'username' => 'required|string|max:50|unique:users,username,' . $userId,
            'password' => 'nullable|string|min:6',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'phone_number' => [
                'required',
                'string',
                'regex:/^(0|\+84)[0-9]{9}$/',
                'unique:users,phone_number,' . $userId,
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.required' => 'Vui lòng nhập :attribute.',
            'username.string' => ':attribute phải là chuỗi ký tự.',
            'username.max' => ':attribute không được vượt quá :max ký tự.',

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
            'email.max' => ':attribute không được vượt quá :max ký tự.',

            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng (ví dụ: 0912345678 hoặc +84912345678).',
        ];
    }
}