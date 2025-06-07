<?php

namespace App\Http\Requests\TableAreaTemplateRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UpdateTableAreaTemplateRequest extends FormRequest
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
        $slug = $this->route('slug');
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                \Illuminate\Validation\Rule::unique('area_templates', 'name')->ignore($slug, 'slug'),
            ],
            'description' => ['nullable', 'string'],
            'slug' => [
                'nullable',
                'string',
                'max:120',
                \Illuminate\Validation\Rule::unique('area_templates', 'slug')->ignore($slug, 'slug'),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->name && !$this->slug) {
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
            'name.required' => 'Tên mẫu khu vực là bắt buộc.',
            'name.string' => 'Tên mẫu khu vực phải là chuỗi.',
            'name.max' => 'Tên mẫu khu vực không được vượt quá :max ký tự.',
            'name.unique' => 'Tên mẫu khu vực đã tồn tại.',
            'description.string' => 'Mô tả phải là chuỗi.',
            'slug.string' => 'Slug phải là chuỗi.',
            'slug.max' => 'Slug không được vượt quá :max ký tự.',
            'slug.unique' => 'Slug đã tồn tại.',
        ];
    }
}
