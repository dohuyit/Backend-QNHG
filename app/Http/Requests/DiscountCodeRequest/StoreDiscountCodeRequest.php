<?php

namespace App\Http\Requests\DiscountCodeRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiscountCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:discount_codes,code|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
            'start_date' => 'nullable|date|before_or_equal:end_date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'usage_limit' => 'nullable|integer|min:1',
            'used' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];
    }

 public function messages(): array
{
    return [
        'code.required' => 'Vui lòng nhập mã giảm giá.',
        'code.string' => 'Mã giảm giá phải là chuỗi ký tự.',
        'code.unique' => 'Mã giảm giá đã tồn tại.',
        'code.max' => 'Mã giảm giá không được vượt quá 50 ký tự.',

        'type.required' => 'Vui lòng chọn loại mã giảm giá.',
        'type.in' => 'Loại mã giảm giá không hợp lệ.',

        'value.required' => 'Vui lòng nhập giá trị giảm giá.',
        'value.numeric' => 'Giá trị giảm phải là số.',
        'value.min' => 'Giá trị phải lớn hơn 0.',

        'start_date.date' => 'Ngày bắt đầu không đúng định dạng ngày.',
        'start_date.before_or_equal' => 'Ngày bắt đầu không được sau ngày kết thúc.',

        'end_date.date' => 'Ngày kết thúc không đúng định dạng ngày.',
        'end_date.after_or_equal' => 'Ngày kết thúc không được trước ngày bắt đầu.',

        'usage_limit.integer' => 'Giới hạn sử dụng phải là số nguyên.',
        'usage_limit.min' => 'Số lần sử dụng tối thiểu là 1.',

        'used.integer' => 'Số lần đã sử dụng phải là số nguyên.',
        'used.min' => 'Số lần đã sử dụng không được nhỏ hơn 0.',

        'is_active.boolean' => 'Trạng thái hoạt động không hợp lệ.',
    ];
}

}
