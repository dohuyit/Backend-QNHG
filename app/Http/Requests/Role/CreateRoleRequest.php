<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseFormRequest;

class CreateRoleRequest extends BaseFormRequest
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
     */
    public function rules()
    {
        return [
            'role_name' => 'required|string|max:100|unique:roles,role_name',
            'description' => 'nullable|string|max:200',
        ];
    }
    public function messages()
    {
        return [
            'role_name.required' => 'Tên vai trò là bắt buộc.',
            'role_name.string' => 'Tên vai trò phải là chuỗi.',
            'role_name.max' => 'Tên vai trò không được vượt quá 100 ký tự.',
            'role_name.unique' => 'Tên vai trò đã tồn tại.',
            'description.string' => 'Mô tả phải là chuỗi.',
            'description.max' => 'Mô tả không được vượt quá 200 ký tự.',
        ];
    }
}
