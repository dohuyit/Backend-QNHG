<?php

namespace App\Http\Requests\DishRequest;

use App\Http\Requests\BaseFormRequest;

class StoreDishRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:dishes,name',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'category_id' => 'required|integer|exists:categories,id',
            'original_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            'is_featured' => 'nullable|boolean',
            'tags' => 'nullable|array|max:255',
            'unit' => 'nullable|in:bowl,plate,cup,glass,large_bowl,other',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên món ăn.',
            'name.string' => 'Tên món ăn phải là chuỗi.',
            'name.max' => 'Tên món ăn không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên món ăn đã tồn tại.',

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

            'status.required' => 'Vui lòng chọn trạng thái hiển thị cho món ăn.',
            'status.in' => 'Trạng thái hiển thị không hợp lệ.',

            'is_featured.boolean' => 'Trạng thái nổi bật phải là true hoặc false.',

            'tags.string' => 'Tags phải là chuỗi.',
            'tags.max' => 'Tags không được vượt quá 255 ký tự.',

            'unit.in' => 'Đơn vị tính không hợp lệ!',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $original = $this->input('original_price');
            $selling = $this->input('selling_price');

            if (is_numeric($original) && is_numeric($selling) && $selling <= $original) {
                $validator->errors()->add('selling_price', 'Giá bán phải lớn hơn giá gốc.');
            }
        });
    }
}
