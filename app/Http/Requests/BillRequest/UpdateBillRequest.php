<?php

namespace App\Http\Requests\BillRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'discount_amount' => 'sometimes|numeric|min:0',
            'delivery_fee'     => 'sometimes|numeric|min:0',
            'status'           => 'sometimes|in:unpaid,paid,cancelled',
        ];
    }

    public function messages(): array
    {
        return [
            'discount_amount.numeric' => 'Tiền giảm giá phải là số.',
            'discount_amount.min'     => 'Tiền giảm giá không được âm.',
            'delivery_fee.numeric'    => 'Phí giao hàng phải là số.',
            'delivery_fee.min'        => 'Phí giao hàng không được âm.',
            'status.in'               => 'Trạng thái hóa đơn không hợp lệ (unpaid, paid, cancelled).',
        ];
    }
}
