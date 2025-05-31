<?php

namespace App\Http\Controllers;

use App\Helpers\ErrorHelper;
use App\Helpers\ResponseHelper;
use Illuminate\Http\Response;

abstract class Controller
{
    /**
     * @param array $data
     * @param array $headers
     * @return Response
     */
    public function responseSuccess(array $data = [], string $message = '', array $headers = [])
    {
        return ResponseHelper::responseSuccess(data: $data, statusCode: 200, headers: $headers);
    }

    /**
     * @param array $errors
     * @param string $code
     * @param string $message
     * @param int $statusCode
     * @param array|null $headers
     * @return Response
     */
    public function responseFail(array $errors = [], string $code = ErrorHelper::FAILED, string $message = '', int $statusCode = 400, ?array $headers = [])
    {
        return ResponseHelper::responseFail(code: $code, errors: $errors, message: $message, statusCode: $statusCode, headers: $headers);
    }
}
