<?php

namespace App\Http\Requests\CartRequest;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'               => 'required|array|min:1',
            'items.*.dish_id'     => 'required|exists:dishes,id',
            'items.*.quantity'    => 'sometimes|nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'             => 'Bạn chưa chọn món ăn nào.',
            'items.array'                => 'Danh sách món ăn không hợp lệ.',
            'items.*.dish_id.required'   => 'Thiếu thông tin món ăn.',
            'items.*.dish_id.exists'     => 'Món ăn không tồn tại.',
            'items.*.quantity.integer'   => 'Số lượng phải là số nguyên.',
            'items.*.quantity.min'       => 'Số lượng tối thiểu là 1.',
        ];
    }
}
