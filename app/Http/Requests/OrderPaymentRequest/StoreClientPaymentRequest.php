<?php

namespace App\Http\Requests\OrderPaymentRequest;

use App\Http\Requests\BaseFormRequest;

class StoreClientPaymentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,momo,vnpay,points,other',
            'notes'          => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
            'payment_method.in'       => 'Phương thức thanh toán không hợp lệ.',

            'notes.string'            => 'Ghi chú phải là chuỗi.',
            'notes.max'               => 'Ghi chú không được vượt quá 255 ký tự.',
        ];
    }
}
