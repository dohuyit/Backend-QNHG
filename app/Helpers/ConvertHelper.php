<?php

namespace App\Helpers;

class ConvertHelper
{
    /**
     * Chuyển chuỗi tag (ngăn cách bởi dấu phẩy) thành JSON array.
     *
     * @return string JSON encoded array
     */
    public static function convertStringToJson(string $stringValue): string
    {
        $tagsArray = array_filter(array_map('trim', explode(',', $stringValue)));

        return json_encode($tagsArray, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Chuyển JSON array tag thành chuỗi ngăn cách bởi dấu phẩy (dùng trong form edit).
     */
    public static function convertJsonToString(string $jsonValue): string
    {
        $tagsArray = json_decode($jsonValue, true);
        if (is_array($tagsArray)) {
            return implode(', ', $tagsArray);
        }

        return '';
    }
}
