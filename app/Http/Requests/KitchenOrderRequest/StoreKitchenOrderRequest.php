<?php

namespace App\Http\Requests\KitchenOrderRequest;

use Illuminate\Foundation\Http\FormRequest;

class StoreKitchenOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'order_item_id' => 'required|integer|exists:order_items,id|unique:kitchen_orders,order_item_id',
            'order_id' => 'required|integer|exists:orders,id',
            'table_number' => 'nullable|string|max:255',
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,preparing,ready,cancelled',
            'is_priority' => 'boolean',
        ];
    }
}
