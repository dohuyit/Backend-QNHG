<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorHelper;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;

abstract class Controller
{
    /**
     * @return Response
     */
    public function responseSuccess(array $data = [], string $message = '', array $headers = [])
    {
        return ResponseHelper::responseSuccess(data: $data, message: $message, statusCode: 200, headers: $headers);
    }

    /**
     * @return Response
     */
    public function responseFail(array $errors = [], string $code = ErrorHelper::FAILED, string $message = '', int $statusCode = 400, ?array $headers = [])
    {
        return ResponseHelper::responseFail(code: $code, errors: $errors, message: $message, statusCode: $statusCode, headers: $headers);
    }
}
