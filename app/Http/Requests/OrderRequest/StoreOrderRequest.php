<?php

namespace App\Http\Requests\OrderRequest;

use App\Http\Requests\BaseFormRequest;

class StoreOrderRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'order_type' => 'required|in:dine-in,takeaway,delivery',
            'reservation_id' => 'nullable|exists:reservations,id',
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.dish_id' => 'nullable|exists:dishes,id',
            'items.*.combo_id' => 'nullable|exists:combos,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
            'items.*.is_priority' => 'boolean',
            'tables' => 'required_if:order_type,dine-in|array',
            'tables.*.table_id' => 'required|exists:tables,id',
            'tables.*.notes' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',

            // validate liên hệ
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|regex:/^[0-9+\-\s]+$/|max:20',
            'contact_email' => 'nullable|email|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'order_type.required' => 'Loại đơn hàng không được để trống',
            'order_type.in' => 'Loại đơn hàng không hợp lệ',
            'reservation_id.exists' => 'Đặt bàn không tồn tại',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'items.required' => 'Danh sách món không được để trống',
            'items.array' => 'Danh sách món không hợp lệ',
            'items.min' => 'Phải có ít nhất 1 món trong đơn hàng',
            'items.*.dish_id.exists' => 'Món ăn không tồn tại',
            'items.*.combo_id.exists' => 'Combo không tồn tại',
            'items.*.quantity.required' => 'Số lượng không được để trống',
            'items.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0',
            'items.*.unit_price.required' => 'Đơn giá không được để trống',
            'items.*.unit_price.numeric' => 'Đơn giá phải là số',
            'items.*.unit_price.min' => 'Đơn giá phải lớn hơn hoặc bằng 0',
            'items.*.notes.max' => 'Ghi chú món không được vượt quá 255 ký tự',
            'tables.required_if' => 'Danh sách bàn không được để trống cho đơn tại bàn',
            'tables.array' => 'Danh sách bàn không hợp lệ',
            'tables.*.table_id.required' => 'ID bàn không được để trống',
            'tables.*.table_id.exists' => 'Bàn không tồn tại',
            'tables.*.notes.max' => 'Ghi chú bàn không được vượt quá 255 ký tự',
            'delivery_address.string' => 'Địa chỉ giao hàng phải là chuỗi',
            'delivery_address.max' => 'Địa chỉ giao hàng không được vượt quá 255 ký tự',
            'notes.max' => 'Ghi chú đơn hàng không được vượt quá 500 ký tự',

            'contact_name.required' => 'Tên người liên hệ không được để trống',
            'contact_name.string' => 'Tên người liên hệ phải là chuỗi',
            'contact_name.max' => 'Tên người liên hệ không được vượt quá 255 ký tự',

            'contact_phone.required' => 'Số điện thoại người liên hệ không được để trống',
            'contact_phone.string' => 'Số điện thoại người liên hệ phải là chuỗi',
            'contact_phone.regex' => 'Số điện thoại người liên hệ không hợp lệ',
            'contact_phone.max' => 'Số điện thoại không được vượt quá 20 ký tự',

            'contact_email.email' => 'Email người liên hệ không hợp lệ',
            'contact_email.max' => 'Email người liên hệ không được vượt quá 255 ký tự',
        ];
    }


    protected function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            foreach ($items as $index => $item) {
                if (empty($item['dish_id']) && empty($item['combo_id'])) {
                    $validator->errors()->add(
                        "items.$index",
                        'Phải chọn món ăn hoặc combo'
                    );
                }
            }
        });
    }
}
