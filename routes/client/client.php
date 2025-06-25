<?php

use App\Http\Controllers\Client\Category\CategoryController;
use App\Http\Controllers\Client\Dish\DishController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\Reservation\ReservationController;

Route::post('reservations/create', [ReservationController::class, 'bookTableByClient']);

Route::get('categories/parent', [CategoryController::class, 'getParentCategories']);
Route::get('categories/child/dish', [CategoryController::class, 'getChildCategoriesByDish']);

Route::get('/dishes/featured', [DishController::class, 'getFeaturedDishes']);
Route::get('/dishes/category/child/{id}', [DishController::class, 'getDishesByChildCategory']);
