<?php

namespace App\Http\Requests\ComboRequest;

use App\Http\Requests\BaseFormRequest;

class UpdateComboRequest extends BaseFormRequest
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
    protected function prepareForValidation()
    {
        $items = $this->input('items');
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $this->merge([
                'items' => $decoded
            ]);
        }
    }
    public function rules()
    {
        $comboId = $this->route('id');
        return [
            'name' => 'required|string|max:255' . $comboId,
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'original_total_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'is_active' => 'required|boolean',
            'items' => 'nullable|array',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập tên combo.',
            'name.string' => 'Tên combo phải là chuỗi.',
            'name.max' => 'Tên combo không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên combo đã tồn tại.',

            'image_url.image' => 'Ảnh phải là tệp hình ảnh.',
            'image_url.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg hoặc webp.',
            'image_url.max' => 'Ảnh không được vượt quá 2MB.',

            'description.string' => 'Mô tả phải là chuỗi.',

            'original_total_price.required' => 'Vui lòng nhập tổng giá gốc cho combo.',
            'original_total_price.numeric' => 'Giá gốc phải là số.',
            'original_total_price.min' => 'Giá gốc không được nhỏ hơn 0.',

            'selling_price.required' => 'Vui lòng nhập giá bán cho combo.',
            'selling_price.numeric' => 'Giá bán phải là số.',
            'selling_price.min' => 'Giá bán không được nhỏ hơn 0.',

            'is_active.required' => 'Vui lòng chọn trạng thái hiển thị cho combo.',
            'is_active.boolean' => 'Trạng thái hiển thị phải là true hoặc false.',

            'items.array' => 'Danh sách món ăn phải là mảng.',

            'items.*.quantity.required' => 'Vui lòng nhập số lượng.',
            'items.*.quantity.integer' => 'Số lượng phải là số nguyên.',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0.',
        ];
    }

}
