<?php

namespace App\Http\Requests\CategoryRequest;

use App\Http\Requests\BaseFormRequest;
class StoreCategoryRequest extends BaseFormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    
     public function rules(): array
     {
        return [
            'parent_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:100|unique:categories,name',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
        ];
     }
     public function messages(): array
     {
        return [
            'parent_id.exists' => 'Danh mục cha không tồn tại.',
            'name.required' => 'Vui lòng nhập tên danh mục.',
            'name.max' => 'Tên danh mục không được vượt quá 100 ký tự.',
            'name.unique' => 'Tên danh mục đã tồn tại.',
            'image_url.image' => 'Ảnh phải là tệp hình ảnh.',
            'image_url.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg hoặc webp.',
            'image_url.max' => 'Ảnh không được vượt quá 2MB.',
            'description.string' => 'Mô tả phải là chuỗi văn bản.',
            'is_active.required' => 'Trạng thái hoạt động là bắt buộc.',
            'is_active.boolean' => 'Trạng thái hoạt động phải là true hoặc false.',
        ];
     }
   
}
