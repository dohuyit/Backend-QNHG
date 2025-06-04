<?php

namespace App\Helpers;

class ResponseHelper
{
    /**
     * @return \Illuminate\Http\Response
     */
    public static function responseSuccess(?array $data = [], string $message = '', int $statusCode = 200, ?array $headers = [])
    {
        $res = [
            'code' => ErrorHelper::SUCCESS,
            'message' => $message ?: ErrorHelper::getMessage(ErrorHelper::SUCCESS),
            'data' => $data,
        ];

        return response($res, $statusCode, $headers);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public static function responseFail(string $code, ?array $errors = [], string $message = '', int $statusCode = 400, ?array $headers = [])
    {
        $errors = [
            'code' => $code,
            'message' => $message ?: NotificationHelper::getMessage($code),
            'errors' => $errors,
        ];

        return response($errors, $statusCode, $headers);
    }

    public static function getResponseFailData(string $code, ?array $errors = [])
    {
        $res = [
            'code' => $code,
            'message' => __("error.$code"),
            'errors' => $errors,
        ];

        return $res;
    }
}
