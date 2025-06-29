<?php

namespace App\Http\Requests\OrderRequest;

use App\Http\Requests\BaseFormRequest;

class UpdateOrderRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $comboId = $this->route('id');
        $rules = [
            'order_type' => 'in:dine-in,takeaway,delivery',
            'reservation_id' => 'nullable|exists:reservations,id',
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'array',
            'items.*.dish_id' => 'required_without:items.*.combo_id|exists:dishes,id',
            'items.*.combo_id' => 'exists:combos,id',
            'items.*.quantity' => 'integer|min:1',
            'items.*.unit_price' => 'numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
            'items.*.is_priority' => 'boolean',
            'tables' => 'array',
            'tables.*.table_id' => 'exists:tables,id',
            'tables.*.notes' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];

        // Chỉ validate name nếu có truyền lên
        if ($this->has('name')) {
            $rules['name'] = 'required|string|max:255|unique:combos,name,' . $comboId;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'order_type.in' => 'Loại đơn hàng không hợp lệ',
            'reservation_id.exists' => 'Đặt bàn không tồn tại',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'items.array' => 'Danh sách món không hợp lệ',
            'items.*.dis_id.exists' => 'Món ăn không tồn tại',
            'items.*.combo_id.exists' => 'Combo không tồn tại',
            'items.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'items.*.quantity.min' => 'Số lượng phải lớn hơn 0',
            'items.*.unit_price.numeric' => 'Đơn giá phải là số',
            'items.*.unit_price.min' => 'Đơn giá phải lớn hơn hoặc bằng 0',
            'items.*.notes.max' => 'Ghi chú món không được vượt quá 255 ký tự',
            'tables.array' => 'Danh sách bàn không hợp lệ',
            'tables.*.table_id.exists' => 'Bàn không tồn tại',
            'tables.*.notes.max' => 'Ghi chú bàn không được vượt quá 255 ký tự',
            'delivery_address.max' => 'Địa chỉ giao hàng không được vượt quá 255 ký tự',
            'notes.max' => 'Ghi chú đơn hàng không được vượt quá 500 ký tự',
        ];
    }
}
