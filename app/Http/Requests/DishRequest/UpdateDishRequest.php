<?php

namespace App\Http\Requests\DishRequest;

use App\Http\Requests\BaseFormRequest;

class UpdateDishRequest extends BaseFormRequest
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
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
            'original_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'is_active' => 'required|boolean',
            'is_featured' => 'nullable|boolean',
            'tags' => 'nullable|string|max:255',
            'unit' => 'nullable|string|max:50',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên món ăn.',
            'name.string' => 'Tên món ăn phải là chuỗi.',
            'name.max' => 'Tên món ăn không được vượt quá 255 ký tự.',

            'image_url.image' => 'Ảnh phải là tệp hình ảnh.',
            'image_url.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg hoặc webp.',
            'image_url.max' => 'Ảnh không được vượt quá 2MB.',

            'description.string' => 'Mô tả phải là chuỗi.',

            'category_id.required' => 'Vui lòng chọn danh mục cho món ăn.',
            'category_id.integer' => 'ID danh mục phải là số.',
            'category_id.exists' => 'Danh mục không tồn tại.',

            'original_price.required' => 'Vui lòng nhập giá gốc cho món ăn.',
            'original_price.numeric' => 'Giá gốc phải là số.',
            'original_price.min' => 'Giá gốc không được nhỏ hơn 0.',

            'selling_price.required' => 'Vui lòng nhập giá bán cho món ăn.',
            'selling_price.numeric' => 'Giá bán phải là số.',
            'selling_price.min' => 'Giá bán không được nhỏ hơn 0.',

            'is_active.required' => 'Vui lòng chọn trạng thái hiển thị cho món ăn.',
            'is_active.boolean' => 'Trạng thái hiển thị phải là true hoặc false.',

            'is_featured.boolean' => 'Trạng thái nổi bật phải là true hoặc false.',

            'tags.string' => 'Tags phải là chuỗi.',
            'tags.max' => 'Tags không được vượt quá 255 ký tự.',

            'unit.string' => 'Đơn vị tính phải là chuỗi.',
            'unit.max' => 'Đơn vị tính không được vượt quá 50 ký tự.',
        ];
    }
}
