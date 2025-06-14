<?php

namespace App\Http\Requests\OrderRequest;

use App\Http\Requests\BaseFormRequest;

class CreateOrderRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'order_type' => 'required|in:dine-in,takeaway,delivery',
            'table_id' => 'required_if:order_type,dine-in|exists:tables,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required_without:items.*.combo_id|exists:menu_items,id',
            'items.*.combo_id' => 'required_without:items.*.menu_item_id|exists:combos,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
            'items.*.is_priority' => 'boolean',
            'tables' => 'required_if:order_type,dine-in|array',
            'tables.*.table_id' => 'required|exists:tables,id',
            'tables.*.notes' => 'nullable|string|max:255',
            'delivery_address' => 'required_if:order_type,delivery|string|max:255',
            'delivery_contact_name' => 'required_if:order_type,delivery|string|max:100',
            'delivery_contact_phone' => 'required_if:order_type,delivery|string|max:20',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'order_type.required' => 'Loại đơn hàng không được để trống',
            'order_type.in' => 'Loại đơn hàng không hợp lệ',
            'table_id.required_if' => 'Bàn không được để trống cho đơn tại bàn',
            'table_id.exists' => 'Bàn không tồn tại',
            'reservation_id.exists' => 'Đặt bàn không tồn tại',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'items.required' => 'Danh sách món không được để trống',
            'items.array' => 'Danh sách món không hợp lệ',
            'items.min' => 'Phải có ít nhất 1 món trong đơn hàng',
            'items.*.menu_item_id.required_without' => 'Phải chọn món ăn hoặc combo',
            'items.*.menu_item_id.exists' => 'Món ăn không tồn tại',
            'items.*.combo_id.required_without' => 'Phải chọn món ăn hoặc combo',
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
            'delivery_address.required_if' => 'Địa chỉ giao hàng không được để trống cho đơn giao đi',
            'delivery_address.max' => 'Địa chỉ giao hàng không được vượt quá 255 ký tự',
            'delivery_contact_name.required_if' => 'Tên người nhận không được để trống cho đơn giao đi',
            'delivery_contact_name.max' => 'Tên người nhận không được vượt quá 100 ký tự',
            'delivery_contact_phone.required_if' => 'Số điện thoại người nhận không được để trống cho đơn giao đi',
            'delivery_contact_phone.max' => 'Số điện thoại người nhận không được vượt quá 20 ký tự',
            'notes.max' => 'Ghi chú đơn hàng không được vượt quá 500 ký tự',
        ];
    }
}
