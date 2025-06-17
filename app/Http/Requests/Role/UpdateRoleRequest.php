<?php

namespace App\Http\Requests\Role;

use App\Http\Requests\BaseFormRequest;

class UpdateRoleRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'role_name' => 'required|string|max:50|unique:roles,role_name,' . $this->route('id'),
            'description' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'role_name.required' => 'Tên vai trò không được để trống.',
            'role_name.unique' => 'Tên vai trò đã tồn tại.',
        ];
    }
}
