<?php

namespace App\Http\Requests\PermissionGroup;

use App\Http\Requests\BaseFormRequest;

class CreatePermissionGroupRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'group_name' => 'required|string|max:100|unique:permission_groups,group_name',
            'description' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'group_name.required' => 'Tên nhóm quyền là bắt buộc.',
            'group_name.max' => 'Tên nhóm quyền không được vượt quá 100 ký tự.',
            'group_name.unique' => 'Tên nhóm quyền đã tồn tại.',
            'description.max' => 'Mô tả không được vượt quá 500 ký tự.',
        ];
    }
}
