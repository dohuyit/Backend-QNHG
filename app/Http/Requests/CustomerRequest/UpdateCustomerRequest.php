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
    public function rules(): array
    {
        return [
            'city_id' => 'required|integer',
            'district_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'image_banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'phone_number' => [
                'required',
                'string',
                'regex:/^(0|\+84)[0-9]{9}$/'
            ],
            'opening_hours' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'status' => 'required|string|in:active,inactive,pending_activation,block',
            'is_main_branch' => 'required|boolean',
            'capacity' => 'nullable|integer|min:0',
            'area_size' => 'nullable|numeric|min:0',
            'number_of_floors' => 'nullable|integer|min:1',
            'url_map' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'main_description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.required' => 'Vui lòng chọn tỉnh/thành phố.',
            'city_id.integer' => 'ID tỉnh/thành phố phải là số.',

            'district_id.required' => 'Vui lòng chọn quận/huyện.',
            'district_id.integer' => 'ID quận/huyện phải là số.',

            'name.required' => 'Vui lòng nhập tên chi nhánh.',
            'name.max' => 'Tên chi nhánh không được vượt quá 255 ký tự.',

            'image_banner.image' => 'Ảnh banner phải là tệp hình ảnh.',
            'image_banner.mimes' => 'Ảnh banner phải có định dạng jpeg, png, jpg hoặc webp.',
            'image_banner.max' => 'Ảnh banner không được vượt quá 2MB.',

            'phone_number.required' => 'Vui lòng nhập số điện thoại.',
            'phone_number.regex' => 'Số điện thoại không hợp lệ. Vui lòng nhập đúng định dạng (ví dụ: 0912345678 hoặc +84912345678).',


            'opening_hours.max' => 'Giờ mở cửa không được vượt quá 255 ký tự.',
            'tags.max' => 'Thẻ tag không được vượt quá 255 ký tự.',

            'status.required' => 'Vui lòng chọn trạng thái hoạt động của chi nhánh.',
            'status.in' => 'Trạng thái không hợp lệ. Chỉ cho phép: active, inactive, temporarily_closed.',

            'is_main_branch.required' => 'Vui lòng xác định chi nhánh chính hay không.',
            'is_main_branch.boolean' => 'Trường chi nhánh chính phải là true hoặc false.',

            'capacity.integer' => 'Sức chứa phải là số nguyên.',
            'capacity.min' => 'Sức chứa không được nhỏ hơn 0.',

            'area_size.numeric' => 'Diện tích phải là số.',
            'area_size.min' => 'Diện tích không được nhỏ hơn 0.',

            'number_of_floors.integer' => 'Số tầng phải là số nguyên.',
            'number_of_floors.min' => 'Số tầng tối thiểu là 1.',

            'url_map.url' => 'Đường dẫn bản đồ không hợp lệ.',
            'url_map.max' => 'Đường dẫn bản đồ không được vượt quá 500 ký tự.',

            'description.string' => 'Mô tả phải là chuỗi ký tự.',
            'main_description.string' => 'Mô tả chính phải là chuỗi ký tự.',
        ];
    }
}
