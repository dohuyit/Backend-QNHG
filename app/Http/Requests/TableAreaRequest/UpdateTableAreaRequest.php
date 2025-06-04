<?php

namespace App\Http\Requests\TableAreaRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateTableAreaRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $tableAreaId = $this->route('slug'); // Lấy slug từ route

        return [
            'branch_id' => ['required', 'exists:branches,id'],
            'name' => ['required', 'string', 'max:255', Rule::unique('table_areas')->ignore($tableAreaId, 'slug')],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('table_areas')->ignore($tableAreaId, 'slug')],
            'description' => ['nullable', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->name && ! $this->slug) {
            $this->merge([
                'slug' => Str::slug($this->name),
            ]);
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'branch_id.required' => 'ID chi nhánh là bắt buộc.',
            'branch_id.exists' => 'ID chi nhánh không tồn tại.',
            'name.required' => 'Tên khu vực bàn là bắt buộc.',
            'name.string' => 'Tên khu vực bàn phải là chuỗi.',
            'name.max' => 'Tên khu vực bàn không được vượt quá :max ký tự.',
            'name.unique' => 'Tên khu vực bàn đã tồn tại.',
            'slug.string' => 'Slug phải là chuỗi.',
            'slug.max' => 'Slug không được vượt quá :max ký tự.',
            'slug.unique' => 'Slug đã tồn tại.',
            'capacity.required' => 'Sức chứa là bắt buộc.',
            'capacity.integer' => 'Sức chứa phải là số nguyên.',
            'capacity.min' => 'Sức chứa phải ít nhất là :min.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.string' => 'Trạng thái phải là chuỗi.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];
    }
}
