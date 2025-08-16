<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => [
        'api/*',
        'admin-face-*', // Thêm dòng này
        'admin/*',
        'sanctum/csrf-cookie'
    ],

    'allowed_methods' => ['*'],

    // CHỈ định cụ thể origin của React app thay vì dùng '*'
    'allowed_origins' => [
        'http://localhost:5173',     // nếu bạn dùng Vite
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
