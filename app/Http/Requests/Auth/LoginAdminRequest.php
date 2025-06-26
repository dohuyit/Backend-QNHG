<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginAdminRequest extends BaseFormRequest
{
     public function authorize(): bool
     {
          return true;
     }

     public function rules(): array
     {
          return [
               'email' => 'required|email',
               'password' => 'required|min:6',
          ];
     }

     public function messages(): array
     {
          return [
               'email.required' => 'Vui lòng nhập email.',
               'email.email' => 'Email không đúng định dạng.',
               'password.required' => 'Vui lòng nhập mật khẩu.',
               'password.min' => 'Mật khẩu phải từ 6 ký tự.',
          ];
     }
}