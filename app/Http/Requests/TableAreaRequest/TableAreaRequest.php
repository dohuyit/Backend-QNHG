<?php

namespace App\Http\Requests\TableAreaRequest;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TableAreaRequest extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('table_areas', 'name')->where(function ($query) {
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
            'image_url' => [
                'nullable',
                'string',
                'max:255',
                'url'
            ],

        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'] = [
                'sometimes',
                'required',
                'string',
                'max:255',
                'min:2',
                Rule::unique('table_areas', 'name')->where(function ($query) {
                    return $query->where('deleted_at', null)
                        ->where('id', '!=', $this->route('id'));
                })
            ];
            $rules['capacity'] = 'sometimes|required|integer|min:1|max:1000';
            $rules['status'] = 'sometimes|required|string|in:active,inactive';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Tên khu vực bàn là bắt buộc',
            'name.max' => 'Tên khu vực bàn không được vượt quá 255 ký tự',
            'name.min' => 'Tên khu vực bàn phải có ít nhất 2 ký tự',
            'name.unique' => 'Tên khu vực bàn đã tồn tại',
            'description.max' => 'Mô tả không được vượt quá 1000 ký tự',
            'capacity.required' => 'Sức chứa là bắt buộc',
            'capacity.integer' => 'Sức chứa phải là số nguyên',
            'capacity.min' => 'Sức chứa phải lớn hơn 0',
            'capacity.max' => 'Sức chứa không được vượt quá 1000 người',
            'status.required' => 'Trạng thái là bắt buộc',
            'status.in' => 'Trạng thái không hợp lệ',
            
        ];
    }
}
