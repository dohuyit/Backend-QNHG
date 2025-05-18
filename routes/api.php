<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {
    return response()->json([
        'status' => 'true',
        'data' => 'Kết nối thành công'
    ]);
});
