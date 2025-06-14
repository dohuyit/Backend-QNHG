<?php

namespace App\Http\Requests\ReservationRequest;

use App\Http\Requests\BaseFormRequest;

class StoreReservationRequest extends BaseFormRequest
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
    public function rules()
    {
        return [
            'customer_id'       => 'nullable|integer|exists:customers,id',
            'customer_name'     => 'required|string|max:100',
            'customer_phone'    => 'required|string|max:20',
            'customer_email'    => 'nullable|email|max:100',
            'reservation_time'  => 'required|date|after:now',
            'number_of_guests'  => 'required|integer|min:1',
            'table_id'          => 'nullable|integer|exists:tables,id',
            'notes'             => 'nullable|string',
            'status'            => 'nullable|in:pending,confirmed,cancelled,completed,no_show,seated',
            'user_id'           => 'nullable|integer|exists:users,id',
        ];
    }
    public function messages()
    {
        return [
            'customer_id.integer'       => 'Mã khách hàng phải là số.',
            'customer_id.exists'        => 'Khách hàng không tồn tại.',

            'customer_name.required'    => 'Vui lòng nhập tên khách.',
            'customer_name.string'      => 'Tên khách phải là chuỗi.',
            'customer_name.max'         => 'Tên khách không được vượt quá 100 ký tự.',

            'customer_phone.required'   => 'Vui lòng nhập số điện thoại.',
            'customer_phone.string'     => 'Số điện thoại phải là chuỗi.',
            'customer_phone.max'        => 'Số điện thoại không được vượt quá 20 ký tự.',

            'customer_email.email'      => 'Email không hợp lệ.',
            'customer_email.max'        => 'Email không được vượt quá 100 ký tự.',

            'reservation_time.required' => 'Vui lòng chọn thời gian đặt.',
            'reservation_time.date'     => 'Thời gian đặt không hợp lệ.',
            'reservation_time.after'    => 'Thời gian đặt phải sau thời điểm hiện tại.',

            'number_of_guests.required' => 'Vui lòng nhập số lượng khách.',
            'number_of_guests.integer'  => 'Số lượng khách phải là số nguyên.',
            'number_of_guests.min'      => 'Phải có ít nhất 1 khách.',

            'table_id.integer'          => 'ID bàn phải là số.',
            'table_id.exists'           => 'Bàn không tồn tại.',

            'notes.string'              => 'Ghi chú phải là chuỗi.',

            'status.in'                 => 'Trạng thái không hợp lệ.',

            'user_id.integer'           => 'Mã nhân viên phải là số.',
            'user_id.exists'            => 'Nhân viên không tồn tại.',
        ];
    }
}
