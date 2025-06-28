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
            'email' => 'required|email|unique:customers,email',
            'password' => 'required|min:6|confirmed',   //password_confirmation 
            'phone_number' => 'required|unique:customers,phone_number|regex:/^0[0-9]{9}$/',
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'Vui lòng nhập họ tên.',
            'full_name.string' => 'Họ tên phải là chuỗi ký tự.',
            'full_name.max' => 'Họ tên không được vượt quá 100 ký tự.',
            'email.required' => 'Vui lòng nhập email.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email đã tồn tại.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải ít nhất 6 ký tự.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.unique' => 'Số điện thoại đã tồn tại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ. Định dạng đúng là 10 số và bắt đầu bằng số 0.',
        ];
    }
}
