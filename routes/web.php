<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\ActivateAccountController;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/activate/{token}', [ActivateAccountController::class, 'activate'])->name('admin.activate');