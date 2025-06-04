<?php

namespace App\Http\Requests;

use App\Helpers\ErrorHelper;
use App\Helpers\ResponseHelper;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->getMessageBag()->toArray();
        $errors = array_map(fn($arr) => reset($arr), $errors);
        $res = ResponseHelper::responseFail(ErrorHelper::FAILED, $errors);
        throw (new HttpResponseException($res));
    }

}
