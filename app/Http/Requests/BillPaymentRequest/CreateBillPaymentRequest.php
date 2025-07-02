<?php

namespace App\Http\Requests\BillPaymentRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateBillPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cho phép luôn, nếu cần thì check role ở đây
    }

    public function rules(): array
    {
        return [
            'bill_id'         => 'required|exists:bills,id',
            'payment_method'  => 'required|in:cash,credit_card,bank_transfer,momo,vnpay,points,other',
            'amount_paid'     => 'required|numeric|min:0',
            'transaction_ref' => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'bill_id.required' => 'Vui lòng chọn hóa đơn cần thanh toán.',
            'bill_id.exists'   => 'Hóa đơn không tồn tại.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in' => 'Phương thức thanh toán không hợp lệ.',
            'amount_paid.required' => 'Vui lòng nhập số tiền thanh toán.',
            'amount_paid.numeric'  => 'Số tiền phải là số.',
            'amount_paid.min'      => 'Số tiền không được nhỏ hơn 0.',
        ];
    }
}
