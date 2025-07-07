<?php

namespace App\Http\Requests\OrderPaymentRequest;

use App\Http\Requests\BaseFormRequest;

class StoreOrderPaymentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method'   => 'required|in:cash,credit_card,bank_transfer,momo,vnpay,points,other',
            'amount_paid'      => 'nullable|numeric|min:0.01',
            'discount_amount'  => 'nullable|numeric|min:0',   
            'delivery_fee'     => 'nullable|numeric|min:0',    
            'notes'            => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'       => 'Phương thức thanh toán không hợp lệ.',

            'amount_paid.required'    => 'Vui lòng nhập số tiền thanh toán.',
            'amount_paid.numeric'     => 'Số tiền phải là số.',
            'amount_paid.min'         => 'Số tiền thanh toán tối thiểu là 0.01.',

            'discount_amount.numeric' => 'Tiền giảm giá phải là số.',
            'discount_amount.min'     => 'Tiền giảm giá không được nhỏ hơn 0.',

            'delivery_fee.numeric'    => 'Phí giao hàng phải là số.',
            'delivery_fee.min'        => 'Phí giao hàng không được nhỏ hơn 0.',

            'notes.string'            => 'Ghi chú phải là chuỗi.',
            'notes.max'               => 'Ghi chú không được vượt quá 500 ký tự.',
        ];
    }
}
