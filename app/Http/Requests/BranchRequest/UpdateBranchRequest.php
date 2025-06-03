<?php

namespace App\Http\Requests\BranchRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBranchRequest extends FormRequest
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
    public function rules()
    {
        return [
            'city_id' => 'required|integer',
            'district_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:branches,slug',
            'image_banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'phone_number' => 'required|string|max:20',
            'opening_hours' => 'nullable|string|max:255',
            'tags' => 'nullable|string|max:255',
            'status' => 'required|in:active,inactive,temporarily_closed',
            'is_main_branch' => 'required|boolean',
            'capacity' => 'nullable|integer|min:0',
            'area_size' => 'nullable|numeric|min:0',
            'number_of_floors' => 'nullable|integer|min:1',
            'url_map' => 'nullable|url|max:500',
            'description' => 'nullable|string',
            'main_description' => 'nullable|string',
        ];
    }
}
