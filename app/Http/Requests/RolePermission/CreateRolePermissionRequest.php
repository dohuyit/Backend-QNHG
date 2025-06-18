<?php

namespace App\Http\Requests\RolePermission;

use App\Http\Requests\BaseFormRequest;

class CreateRolePermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role_id' => 'required|exists:roles,id',
            'permission_id' => 'required|exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.required' => 'Vui lòng chọn vai trò.',
            'role_id.exists' => 'Vai trò không tồn tại.',
            'permission_id.required' => 'Vui lòng chọn quyền.',
            'permission_id.exists' => 'Quyền không tồn tại.',
        ];
    }
}
