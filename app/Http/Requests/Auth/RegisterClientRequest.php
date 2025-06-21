<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class RegisterClientRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:100',
            'username' => 'required|string|max:100|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',   //password_confirmation 
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'full_name.string' => 'Họ tên phải là chuỗi ký tự.',
            'full_name.max' => 'Họ tên không được vượt quá 100 ký tự.',
            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.string' => 'Tên  đăng nhập phải là chuỗi ký tự.',
            'username.max' => 'Tên đăng nhập không được vượt quá 100 ký tự.',
            'username.unique' => 'Tên đăng nhập đã được sử dụng.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
