<?php

namespace App\Http\Requests\TableRequest;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TableRequest extends BaseFormRequest
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
        $rules = [
            'table_number' => [
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('tables', 'table_number')->where(function ($query) {
                    return $query->where('deleted_at', null);
                })
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'capacity' => [
                'required',
                'integer',
                'min:1',
                'max:1000'
            ],
            'min_guests' => [
                'required',
                'integer',
                'min:1',
                'max:1000'
            ],
            'max_guests' => [
                'required',
                'integer',
                'min:1',
                'max:1000'
            ],
            'tags' => [
                'nullable',

            ],
            'status' => [
                'required',
                'string',
                Rule::in(['available', 'occupied', 'reserved', 'cleaning', 'out_of_service'])
            ],
            'is_active' => [
                'required',
                'boolean'
            ],
            'table_area_id' => [
                'required',
                'exists:table_areas,id'
            ]
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['table_number'] = [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('tables', 'table_number')->where(function ($query) {
                    return $query->where('deleted_at', null)
                        ->where('id', '!=', $this->route('id'));
                })
            ];
            $rules['capacity'] = 'sometimes|required|integer|min:1|max:1000';
            $rules['min_guests'] = 'sometimes|required|integer|min:1|max:1000';
            $rules['max_guests'] = 'sometimes|required|integer|min:1|max:1000';
            $rules['status'] = 'sometimes|required|string|in:available,occupied,reserved,cleaning,out_of_service';
            $rules['is_active'] = 'sometimes|required|boolean';
            $rules['table_area_id'] = 'sometimes|required|exists:table_areas,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'table_number.required' => 'Số bàn không được để trống',
            'table_number.string' => 'Số bàn phải là chuỗi ký tự',
            'table_number.max' => 'Số bàn không được vượt quá 255 ký tự',
            'table_number.min' => 'Số bàn phải có ít nhất 2 ký tự',
            'table_number.unique' => 'Số bàn đã tồn tại',
            'description.string' => 'Mô tả phải là chuỗi ký tự',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự',
            'capacity.required' => 'Sức chứa không được để trống',
            'capacity.integer' => 'Sức chứa phải là số nguyên',
            'capacity.min' => 'Sức chứa phải lớn hơn 0',
            'capacity.max' => 'Sức chứa không được vượt quá 1000',
            'min_guests.required' => 'Số khách tối thiểu không được để trống',
            'min_guests.integer' => 'Số khách tối thiểu phải là số nguyên',
            'min_guests.min' => 'Số khách tối thiểu phải lớn hơn 0',
            'min_guests.max' => 'Số khách tối thiểu không được vượt quá 1000',
            'max_guests.required' => 'Số khách tối đa không được để trống',
            'max_guests.integer' => 'Số khách tối đa phải là số nguyên',
            'max_guests.min' => 'Số khách tối đa phải lớn hơn 0',
            'max_guests.max' => 'Số khách tối đa không được vượt quá 1000',
            'tags.array' => 'Tags phải là một mảng',
            'status.required' => 'Trạng thái không được để trống',
            'status.string' => 'Trạng thái phải là chuỗi ký tự',
            'status.in' => 'Trạng thái không hợp lệ',
            'is_active.required' => 'Trạng thái hoạt động không được để trống',
            'is_active.boolean' => 'Trạng thái hoạt động không hợp lệ',
            'table_area_id.required' => 'Khu vực bàn không được để trống',
            'table_area_id.exists' => 'Khu vực bàn không tồn tại'
        ];
    }
}
