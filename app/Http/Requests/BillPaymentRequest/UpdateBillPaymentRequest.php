<?php

namespace App\Http\Requests\BillPaymentRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBillPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // check quyền nếu có phân role
    }

    public function rules(): array
    {
        return [
            'payment_method'  => 'required|in:cash,credit_card,bank_transfer,momo,vnpay,points,other',
            'amount_paid'     => 'required|numeric|min:0',
            'transaction_ref' => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
            'payment_time'    => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'       => 'Phương thức thanh toán không hợp lệ.',
            'amount_paid.required'    => 'Vui lòng nhập số tiền thanh toán.',
            'amount_paid.numeric'     => 'Số tiền phải là số.',
            'amount_paid.min'         => 'Số tiền không được nhỏ hơn 0.',
            'payment_time.date'       => 'Thời gian thanh toán phải là ngày hợp lệ.',
        ];
    }
}
