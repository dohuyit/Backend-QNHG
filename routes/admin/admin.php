<?php

use App\Http\Controllers\Admin\TableAreaController;
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
    Route::post('customers/create', [CustomerController::class, 'createCustomer']);
    Route::post('customers/{id}/update', [CustomerController::class, 'updateCustomer']);
    Route::get('customers/trash', [CustomerController::class, 'listTrashedCustomer']);
    Route::delete('customers/{id}/soft/delete', [CustomerController::class, 'softDeleteCustomer']);
    Route::delete('customers/{id}/force/delete', [CustomerController::class, 'forceDeleteCustomer']);
    Route::post('customers/{id}/restore', [CustomerController::class, 'restoreCustomer']);

    // # table areas templates
    Route::get('table-areas-templates/list', [TableAreaTemplateController::class, 'getListTableAreasTemplate']);
    Route::get('table-areas-templates/{slug}/detail', [TableAreaTemplateController::class, 'getTableAreaTemplateDetail']);
    Route::post('table-areas-templates/create', [TableAreaTemplateController::class, 'createTableAreaTemplate']);
    Route::post('table-areas-templates/{slug}/update', [TableAreaTemplateController::class, 'updateTableAreaTemplate']);
    Route::delete('table-areas-templates/{slug}/delete', [TableAreaTemplateController::class, 'deleteTableAreaTemplate']);

    // # table areas
    Route::get('table-areas/list/{branchId}', [TableAreaController::class, 'getTableAreasByBranch']);
    Route::post('table-areas/create/{branchId}', [TableAreaController::class, 'createTableAreaForBranch']);
    Route::post('table-areas/{slug}/update/{branchId}', [TableAreaController::class, 'updateTableAreaForBranch']);
    Route::post('table-areas/create-for-all-branches', [TableAreaController::class, 'createTableAreaForAllBranches']);
    Route::get('table-areas/{slug}/detail', [TableAreaController::class, 'getTableAreaDetail']);
    Route::delete('table-areas/{slug}/delete/{branchId}', [TableAreaController::class, 'deleteTableAreaForBranch']);
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

    ##resetpass
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password/{id}', [AuthController::class, 'resetPassword']);
});
