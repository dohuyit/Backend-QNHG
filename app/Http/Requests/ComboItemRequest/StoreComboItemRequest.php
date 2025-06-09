<?php

namespace App\Http\Requests\ComboItemRequest;

use App\Http\Requests\BaseFormRequest;
class StoreComboItemRequest extends BaseFormRequest
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
            'combo_id' => 'required|integer|exists:combos,id',
            'dish_id' => 'required|integer|exists:dishes,id',
            'quantity' => 'required|integer|min:1',
        ];
     }
    public function messages(): array
     {
        return [
            'combo_id.required' => 'Vui lòng chọn combo',
            'combo_id.exists' => 'Combo không tồn tại!',
            'dish_id.required' => 'Vui lòng chọn món ăn!',
            'dish_id.exists' => 'Món ăn không tồn tại!',
            'quantity.required' => 'Vui lòng nhập số lượng!',
            'quantity.integer' => 'Số lượng phải là số nguyên!',
            'quantity.min' => 'Số lượng phải lớn hơn 0!',
        ];
     }
}
