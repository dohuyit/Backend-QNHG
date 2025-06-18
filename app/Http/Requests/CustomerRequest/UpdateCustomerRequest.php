<?php

namespace App\Http\Requests\CustomerRequest;

use App\Http\Requests\BaseFormRequest;

class UpdateCustomerRequest extends BaseFormRequest
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
            'full_name'      => 'required|string|max:255',
            'avatar'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'phone_number' => [
                'required',
                'string',
                'regex:/^(0|\+84)[0-9]{9}$/'
            ],
            'email' => 'required|email|max:255|unique:customers,email,' . $this->route('id'),
            'address'        => 'nullable|string|max:500',
            'gender'         => 'nullable|in:male,female,other',
            'date_of_birth'  => 'nullable|date|before:today',
            'city_id'        => 'required|string|max:10',
            'district_id'    => 'required|string|max:10',
            'ward_id'        => 'required|string|max:10',
            'notes'          => 'nullable|string',
            'status_customer'         => 'required|in:active,inactive,blocked',
        ];
    }

    public function messages()
    {
        return [
            'full_name.required'    => 'Vui lòng nhập họ và tên.',
            'full_name.max'         => 'Họ và tên không được vượt quá 255 ký tự.',

            'avatar.image'          => 'Ảnh đại diện phải là tệp hình ảnh.',
            'avatar.mimes'          => 'Ảnh đại diện phải có định dạng jpeg, png, jpg hoặc webp.',
            'avatar.max'            => 'Ảnh đại diện không được vượt quá 2MB.',

            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng (ví dụ: 0912345678 hoặc +84912345678).',

            'email.required'        => 'Vui lòng nhập email.',
            'email.email'           => 'Email không hợp lệ.',
            'email.max'             => 'Email không được vượt quá 255 ký tự.',
            'email.unique'          => 'Email đã tồn tại.',

            'address.max'           => 'Địa chỉ không được vượt quá 500 ký tự.',

            'gender.in'             => 'Giới tính không hợp lệ. Giá trị cho phép: male, female, other.',

            'date_of_birth.date'    => 'Ngày sinh không hợp lệ.',
            'date_of_birth.before'  => 'Ngày sinh phải nhỏ hơn ngày hiện tại.',

            'city_id.required'      => 'Vui lòng chọn tỉnh/thành phố.',
            'city_id.max'           => 'Mã tỉnh/thành phố không được vượt quá 10 ký tự.',

            'district_id.required'  => 'Vui lòng chọn quận/huyện.',
            'district_id.max'       => 'Mã quận/huyện không được vượt quá 10 ký tự.',

            'ward_id.required'      => 'Vui lòng chọn phường/xã.',
            'ward_id.max'           => 'Mã phường/xã không được vượt quá 10 ký tự.',

            'status_customer.required'       => 'Vui lòng chọn trạng thái khách hàng.',
            'status_customer.in'             => 'Trạng thái không hợp lệ. Giá trị cho phép: active, inactive, blocked.',
        ];
    }
}
