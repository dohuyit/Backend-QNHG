<?php

use App\Http\Controllers\Client\Auth\AuthController;
use Illuminate\Support\Facades\Route;


// http://127.0.0.1:8000/api/register
Route::post('/register', [AuthController::class, 'register']);

// http://127.0.0.1:8000/api/login
Route::post('/login', [AuthController::class, 'login']);

// http://127.0.0.1:8000/api/logout
Route::middleware('auth:customer')->post('/logout', [AuthController::class, 'logout']);

