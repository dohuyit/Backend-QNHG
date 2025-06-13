<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Category\CategoryController;
use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\User\UserController;
use App\Http\Controllers\Admin\Table\TableController;
use App\Http\Controllers\Admin\Customer\CustomerController;
use App\Http\Controllers\Admin\TableArea\TableAreaController;
use App\Http\Controllers\Admin\Combo\ComboController;
use App\Http\Controllers\Admin\Dish\DishController;
use App\Http\Controllers\Admin\Reservation\ReservationController;

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

    ## tables
    Route::get('tables/list', [TableController::class, 'getListTables']);
    Route::get('tables/{id}/detail', [TableController::class, 'getTableDetail']);
    Route::post('tables/create', [TableController::class, 'createTable']);
    Route::post('tables/{id}/update', [TableController::class, 'updateTable']);
    Route::delete('tables/{id}/delete', [TableController::class, 'destroyTable']);


    ##resetpass
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password/{id}', [AuthController::class, 'resetPassword']);

    // dishes
    Route::get('dishes/list', [DishController::class, 'getListDishes']);
    Route::get('dishes/{id}/detail', [DishController::class, 'getDishDetail']); 
    Route::get('dishes/category/{id}', [DishController::class, 'getDishesByCategory']);
    Route::post('dishes/create', [DishController::class, 'createDish']); 
    Route::post('dishes/{id}/update', [DishController::class, 'updateDish']);
    Route::get('dishes/trash', [DishController::class, 'listTrashedDish']);
    Route::delete('dishes/{id}/soft/delete', [DishController::class, 'softDeleteDish']);
    Route::delete('dishes/{id}/force/delete', [DishController::class, 'forceDeleteDish']);
    Route::post('dishes/{id}/restore', [DishController::class, 'restoreDish']);

    // combos
    Route::get('combos/list', [ComboController::class, 'getListCombos']);
    Route::get('combos/{id}/detail', [ComboController::class, 'getComboDetail']);
    Route::post('combos/create', [ComboController::class, 'createCombo']);
    Route::post('combos/{id}/update', [ComboController::class, 'updateCombo']);
    Route::get('combos/trash', [ComboController::class, 'listTrashedCombo']);
    Route::delete('combos/{id}/soft/delete', [ComboController::class, 'softDeleteCombo']);
    Route::delete('combos/{id}/force/delete', [ComboController::class, 'forceDeleteCombo']);
    Route::post('combos/{id}/restore', [ComboController::class, 'restoreCombo']);

    Route::post('combos/{id}/add-items', [ComboController::class, 'addItemToCombo']);
    Route::post('combos/{comboId}/{dishId}/update-quantity', [ComboController::class, 'updateItemQuantity']);
    Route::delete('combos/{comboId}/{dishId}/force/delete', [ComboController::class, 'forceDeleteComboItem']);

    // Reservation
    Route::get('reservations/list', [ReservationController::class, 'getListReservations']);
    Route::get('reservations/{id}/detail', [ReservationController::class, 'getReservationDetail']);
    Route::post('reservations/create', [ReservationController::class, 'createReservation']);
    Route::post('reservations/{id}/update', [ReservationController::class, 'updateReservation']);
    Route::get('reservations/trash', [ReservationController::class, 'listTrashedReservation']);
    Route::delete('reservations/{id}/soft/delete', [ReservationController::class, 'softDeleteReservation']);
    Route::delete('reservations/{id}/force/delete', [ReservationController::class, 'forceDeleteReservation']);
    Route::post('reservations/{id}/restore', [ReservationController::class, 'restoreReservation']);

    


});