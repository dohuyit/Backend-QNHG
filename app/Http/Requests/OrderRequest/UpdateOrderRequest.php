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

        return [
            'order_type' => 'in:dine-in,takeaway,delivery',
            'reservation_id' => 'nullable|exists:reservations,id',
            'customer_id' => 'nullable|exists:customers,id',
            'status' => 'nullable|in:pending,confirmed,preparing,ready,served,delivering,completed,cancelled',
            'items' => 'required|array|min:1',
            'items.*.dish_id' => 'nullable|exists:dishes,id', // dish_id không required, sẽ tự check ở withValidator
            'items.*.combo_id' => 'nullable|exists:combos,id', // combo_id cũng nullable, sẽ tự check
            'items.*.quantity' => 'integer',
            'items.*.unit_price' => 'numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
            'items.*.is_priority' => 'boolean',
            'tables' => 'required|array|min:1',
            'tables.*.table_id' => 'exists:tables,id',
            'tables.*.notes' => 'nullable|string|max:255',
            'delivery_address' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:500',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);
            $tables = $this->input('tables', []);
            $status = $this->input('status', null);

            // Kiểm tra từng item: phải có dish hoặc combo
            foreach ($items as $index => $item) {
                if (empty($item['dish_id']) && empty($item['combo_id'])) {
                    $validator->errors()->add(
                        "items.$index",
                        'Phải chọn món ăn hoặc combo'
                    );
                }
            }

            // Kiểm tra trạng thái với điều kiện chọn món & bàn
            if (!empty($items) && !empty($tables) && ($status === 'pending' || $status === null)) {
                $validator->errors()->add(
                    'status',
                    'Vui lòng cập nhật trạng thái đơn hàng sang đã xác nhận khi đã chọn món và bàn.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'order_type.in' => 'Loại đơn hàng không hợp lệ',
            'reservation_id.exists' => 'Đặt bàn không tồn tại',
            'customer_id.exists' => 'Khách hàng không tồn tại',
            'status.in' => 'Trạng thái đơn hàng không hợp lệ',
            'items.required' => 'Danh sách món không được bỏ trống',
            'items.min' => 'Phải chọn ít nhất 1 món',
            'items.array' => 'Danh sách món không hợp lệ',
            'items.*.dish_id.exists' => 'Món ăn không tồn tại',
            'items.*.combo_id.exists' => 'Combo không tồn tại',
            'items.*.quantity.integer' => 'Số lượng phải là số nguyên',
            'items.*.unit_price.numeric' => 'Đơn giá phải là số',
            'items.*.unit_price.min' => 'Đơn giá phải lớn hơn hoặc bằng 0',
            'items.*.notes.max' => 'Ghi chú món không được vượt quá 255 ký tự',
            'tables.required' => 'Danh sách bàn không được bỏ trống',
            'tables.min' => 'Phải chọn ít nhất 1 bàn',
            'tables.array' => 'Danh sách bàn không hợp lệ',
            'tables.*.table_id.exists' => 'Bàn không tồn tại',
            'tables.*.notes.max' => 'Ghi chú bàn không được vượt quá 255 ký tự',
            'delivery_address.max' => 'Địa chỉ giao hàng không được vượt quá 255 ký tự',
            'notes.max' => 'Ghi chú đơn hàng không được vượt quá 500 ký tự',
        ];
    }
}
