<?php

namespace App\Http\Requests\TableAreaRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class CreateBranchTableAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'area_template_id' => ['nullable', 'exists:area_templates,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'capacity' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->name && !$this->slug) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'branch_id.required' => 'ID chi nhánh là bắt buộc.',
            'branch_id.exists' => 'Chi nhánh không tồn tại.',
            'area_template_id.exists' => 'Mẫu khu vực không tồn tại.',
            'name.required' => 'Tên khu vực bàn là bắt buộc.',
            'name.string' => 'Tên khu vực bàn phải là chuỗi.',
            'name.max' => 'Tên khu vực bàn không được vượt quá :max ký tự.',
            'description.string' => 'Mô tả phải là chuỗi.',
            'description.max' => 'Mô tả không được vượt quá :max ký tự.',
            'status.string' => 'Trạng thái phải là chuỗi.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'capacity.integer' => 'Sức chứa phải là số nguyên.',
            'capacity.min' => 'Sức chứa không được nhỏ hơn 0.',
        ];
    }
}
