<?php

namespace App\Http\Requests\TableRequest;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateTableRequest extends BaseFormRequest
{
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
            'table_number' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                'min:2',
                Rule::unique('tables', 'table_number')
                    ->where(function ($query) {
                        return $query->where('deleted_at', null);
                    })
                    ->ignore($this->route('id')),
            ],
            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000'
            ],
            'table_type' => [
                'sometimes',
                'required',
                'string',
                Rule::in(['2_seats', '4_seats', '8_seats'])
            ],
            'tags' => [
                'sometimes',
                'nullable',
                'array'
            ],
            'status' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['available', 'occupied', 'cleaning', 'out_of_service'])
            ],
            'table_area_id' => [
                'sometimes',
                'required',
                'exists:table_areas,id'
            ]
        ];
    }

    public function messages()
    {
        return [
            'table_number.required' => 'Số bàn không được để trống',
            'table_number.string' => 'Số bàn phải là chuỗi ký tự',
            'table_number.max' => 'Số bàn không được vượt quá 50 ký tự',
            'table_number.min' => 'Số bàn phải có ít nhất 2 ký tự',
            'table_number.unique' => 'Số bàn đã tồn tại',
            'description.string' => 'Mô tả phải là chuỗi ký tự',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự',
            'table_type.required' => 'Loại bàn không được để trống',
            'table_type.string' => 'Loại bàn phải là chuỗi ký tự',
            'table_type.in' => 'Loại bàn không hợp lệ (chỉ cho phép: 2 ghế, 4 ghế, 8 ghế)',
            'tags.array' => 'Tags phải là một mảng',
            'status.string' => 'Trạng thái phải là chuỗi ký tự',
            'status.in' => 'Trạng thái không hợp lệ',
            'table_area_id.required' => 'Khu vực bàn không được để trống',
            'table_area_id.exists' => 'Khu vực bàn không tồn tại'
        ];
    }
}