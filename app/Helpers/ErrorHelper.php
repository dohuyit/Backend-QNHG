<?php

namespace App\Helpers;

class ErrorHelper
{
    const SUCCESS = 'SUCCESS';

    const FAILED = 'FAILED';

    const SERVER_ERROR = 'SERVER_ERROR';

    const TOO_MANY_REQUEST = 'TOO_MANY_REQUEST';

    const REQUEST_TIMEOUT = 'REQUEST_TIMEOUT';

    const URL_NOT_EXIST = 'URL_NOT_EXIST';

    const INVALID_METHOD = 'INVALID_METHOD';

    const INVALID_FILE_SIZE = 'INVALID_FILE_SIZE';

    const INVALID_REQUEST_FORMAT = 'INVALID_REQUEST_FORMAT';

    const INVALID_AUTH_TOKEN = 'INVALID_AUTH_TOKEN';

    const HTTP_UNAUTHORIZED = 'HTTP_UNAUTHORIZED';

    public static function getError(string $errorCode, string $customMessage = '')
    {
        $res = [
            'code' => $errorCode,
            'message' => $customMessage ?: __("error.$errorCode"),
        ];

        return $res;
    }

    public static function getMessage(string $errorCode): string
    {
        return __("error.$errorCode");
    }
}
