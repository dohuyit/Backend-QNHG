<?php

namespace App\Helpers;

class ConvertHelper
{
    /**
     * Chuyển chuỗi tag (ngăn cách bởi dấu phẩy) thành JSON array.
     *
     * @return string JSON encoded array
     */
    public static function convertArrayToJson(array $tags): string
    {
        // Duyệt và trim từng phần tử, loại bỏ phần tử rỗng
        $tagsArray = array_filter(array_map('trim', $tags));

        // Chuyển sang JSON, không mã hóa Unicode
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
