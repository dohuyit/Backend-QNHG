<?php

namespace App\Http\Requests\Permission;

use App\Http\Requests\BaseFormRequest;

class CreatePermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permission_name' => 'required|string|max:100|unique:permissions,permission_name',
            'description' => 'nullable|string|max:200',
            'permission_group_id' => 'required|exists:permission_groups,id',
        ];
    }
    public function messages(): array
    {
        return [
            'permission_name.required' => 'Vui lòng nhập :attribute.',
            'permission_name.string' => ':attribute phải là chuỗi ký tự.',
            'permission_name.max' => ':attribute không được vượt quá :max ký tự.',
            'permission_name.unique' => ':attribute đã tồn tại trong hệ thống.',

            'description.string' => ':attribute phải là chuỗi ký tự.',

            'permission_group_id.required' => 'Vui lòng chọn :attribute.',
            'permission_group_id.exists' => ':attribute không tồn tại.',
        ];
    }
    public function attributes(): array
    {
        return [
            'permission_name' => 'Tên quyền',
            'description' => 'Mô tả',
            'permission_group_id' => 'Nhóm quyền',
        ];
    }
}
