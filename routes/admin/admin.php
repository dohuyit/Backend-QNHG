<?php

use App\Http\Controllers\Admin\TableAreaController;
use App\Http\Controllers\Admin\TableAreaTemplateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ComboController;
use App\Http\Controllers\Admin\ComboItemController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\DishController;
use App\Http\Controllers\Admin\User\UserController;
use Illuminate\Support\Facades\Route;

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

    // dishes
    Route::get('dishes/list', [DishController::class, 'getListDishes']);
    Route::get('dishes/{slug}/detail', [DishController::class, 'getDishDetail']); 
    Route::get('dishes/category/{slug}', [DishController::class, 'getDishesByCategory']);
    Route::post('dishes/create', [DishController::class, 'createDish']); 
    Route::post('dishes/{slug}/update', [DishController::class, 'updateDish']);
    Route::get('dishes/trash', [DishController::class, 'listTrashedDish']);
    Route::delete('dishes/{slug}/soft/delete', [DishController::class, 'softDeleteDish']);
    Route::delete('dishes/{slug}/force/delete', [DishController::class, 'forceDeleteDish']);
    Route::post('dishes/{slug}/restore', [DishController::class, 'restoreDish']);

    // combos
    Route::get('combos/list', [ComboController::class, 'getListCombos']);
    Route::get('combos/{slug}/detail', [ComboController::class, 'getComboDetail']);
    Route::post('combos/create', [ComboController::class, 'createCombo']);
    Route::post('combos/{slug}/update', [ComboController::class, 'updateCombo']);
    Route::get('combos/trash', [ComboController::class, 'listTrashedCombo']);
    Route::delete('combos/{slug}/soft/delete', [ComboController::class, 'softDeleteCombo']);
    Route::delete('combos/{slug}/force/delete', [ComboController::class, 'forceDeleteCombo']);
    Route::post('combos/{slug}/restore', [ComboController::class, 'restoreCombo']);

    // combo Items
    Route::get('combo-items/list', [ComboItemController::class, 'getListComboItems']);
    Route::post('combo-items/add-items', [ComboItemController::class, 'addItemToCombo']);
    Route::post('combo-items/{comboSlug}/{dishSlug}/update-quantity', [ComboItemController::class, 'updateItemQuantity']);
    Route::delete('combo-items/{comboSlug}/{dishSlug}/force/delete', [ComboItemController::class, 'forceDeleteComboItem']);

});