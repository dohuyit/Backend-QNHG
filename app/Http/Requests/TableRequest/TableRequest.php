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
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('tables', 'name')->where(function ($query) {
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
            'status' => [
                'required',
                'string',
                Rule::in(['active', 'inactive'])
            ],
            'table_area_id' => [
                'required',
                'exists:table_areas,id'
            ]
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'] = [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('tables', 'name')->where(function ($query) {
                    return $query->where('deleted_at', null)
                        ->where('id', '!=', $this->route('id'));
                })
            ];
            $rules['capacity'] = 'sometimes|required|integer|min:1|max:1000';
            $rules['status'] = 'sometimes|required|string|in:active,inactive';
            $rules['table_area_id'] = 'sometimes|required|exists:table_areas,id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên bàn không được để trống',
            'name.string' => 'Tên bàn phải là chuỗi ký tự',
            'name.max' => 'Tên bàn không được vượt quá 255 ký tự',
            'name.min' => 'Tên bàn phải có ít nhất 2 ký tự',
            'name.unique' => 'Tên bàn đã tồn tại',
            'description.string' => 'Mô tả phải là chuỗi ký tự',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự',
            'capacity.required' => 'Sức chứa không được để trống',
            'capacity.integer' => 'Sức chứa phải là số nguyên',
            'capacity.min' => 'Sức chứa phải lớn hơn 0',
            'capacity.max' => 'Sức chứa không được vượt quá 1000',
            'status.required' => 'Trạng thái không được để trống',
            'status.string' => 'Trạng thái phải là chuỗi ký tự',
            'status.in' => 'Trạng thái không hợp lệ',
            'table_area_id.required' => 'Khu vực bàn không được để trống',
            'table_area_id.exists' => 'Khu vực bàn không tồn tại'
        ];
    }
}
