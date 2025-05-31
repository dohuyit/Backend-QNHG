<?php

namespace App\Helpers;

class ResponseHelper
{
    /**
     * @param array|null $data
     * @param int $statusCode
     * @param array|null $headers
     * @return \Illuminate\Http\Response
     */
    public static function responseSuccess(?array $data = [], string $message = '', int $statusCode = 200, ?array $headers = [])
    {
        $res = [
            'code' => NotificationHelper::SUCCESS,
            'message' => $message ?: NotificationHelper::getMessage(NotificationHelper::SUCCESS),
            'data' => $data,
        ];
        return response($res, $statusCode, $headers);
    }

    /**
     * @param string $code
     * @param array|null $errors
     * @param string $message
     * @param int $statusCode
     * @param array|null $headers
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
