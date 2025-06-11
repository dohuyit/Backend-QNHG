<?php

use App\Http\Controllers\Admin\TableArea\TableAreaController;
use App\Http\Controllers\Admin\TableAreaTemplateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\AuthController;

Route::prefix('admin')->group(function () {
    // # branchs
    Route::get('customers/list', [CustomerController::class, 'getListCustomers']);
    Route::get('customers/{id}/detail', [CustomerController::class, 'getCustomerDetail']);

    Route::post('customers/{id}/update', [CustomerController::class, 'updateCustomer']);
    Route::get('customers/trash', [CustomerController::class, 'listTrashedCustomer']);
    Route::delete('customers/{id}/soft/delete', [CustomerController::class, 'softDeleteCustomer']);
    Route::delete('customers/{id}/force/delete', [CustomerController::class, 'forceDeleteCustomer']);
    Route::post('customers/{id}/restore', [CustomerController::class, 'restoreCustomer']);


    ## categories
    Route::get('categories/list', [CategoryController::class, 'getListCategories']);
    Route::get('categories/{slug}/detail', [CategoryController::class, 'getCategoryDetail']);
    Route::post('categories/create', [CategoryController::class, 'createCategory']);
    Route::post('categories/{slug}/update', [CategoryController::class, 'updateCategory']);
    Route::get('categories/trash', [CategoryController::class, 'listTrashedCategory']);
    Route::delete('categories/{slug}/soft/delete', [CategoryController::class, 'softDeleteCategory']);
    Route::delete('categories/{slug}/force/delete', [CategoryController::class, 'forceDeleteCategory']);
    Route::post('categories/{slug}/restore', [CategoryController::class, 'restoreCategory']);

    ##users
    Route::post('users/create', [UserController::class, 'createUser']);
    Route::post('users/{id}/update', [UserController::class, 'updateUser']);
    Route::get('users/list', [UserController::class, 'getListUser']);
    Route::get('users/{id}/delete', [UserController::class, 'deleteUser']);
    Route::post('users/{id}/block', [UserController::class, 'blockUser']);
    Route::post('users/{id}/unblock', [UserController::class, 'unblockUser']);

    ## table areas
    Route::get('table-areas/list', [TableAreaController::class, 'getListTableArea']);
    Route::get('table-areas/{id}/detail', [TableAreaController::class, 'getTableAreaDetail']);
    Route::post('table-areas/create', [TableAreaController::class, 'createTableArea']);
    Route::post('table-areas/{id}/update', [TableAreaController::class, 'updateTableArea']);
    Route::delete('table-areas/{id}/delete', [TableAreaController::class, 'destroy']);

    ##resetpass
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password/{id}', [AuthController::class, 'resetPassword']);
});
