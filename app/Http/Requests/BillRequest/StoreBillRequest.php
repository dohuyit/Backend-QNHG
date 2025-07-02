<?php

namespace App\Http\Requests\BillRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id'         => 'required|exists:orders,id|unique:bills,order_id',
            'discount_amount'  => 'sometimes|numeric|min:0',
            'delivery_fee'     => 'sometimes|numeric|min:0',
            'status'           => 'sometimes|in:unpaid,paid,cancelled',
            'bill_code'        => 'sometimes|string|max:20|unique:bills,bill_code',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Vui lòng chọn đơn hàng.',
            'order_id.exists'   => 'Đơn hàng không tồn tại.',
            'order_id.unique'   => 'Hóa đơn cho đơn hàng này đã tồn tại.',

            'discount_amount.numeric' => 'Tiền giảm giá phải là số.',
            'discount_amount.min'     => 'Tiền giảm giá không được âm.',

            'delivery_fee.numeric'    => 'Phí giao hàng phải là số.',
            'delivery_fee.min'        => 'Phí giao hàng không được âm.',

            'status.in'               => 'Trạng thái hóa đơn không hợp lệ (unpaid, paid, cancelled).',
            'bill_code.unique'        => 'Mã hóa đơn đã tồn tại.',
        ];
    }
}
