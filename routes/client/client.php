<?php

use App\Http\Controllers\Client\Category\CategoryController;
use App\Http\Controllers\Client\Dish\DishController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\Reservation\ReservationController;

// http://127.0.0.1:8000/api/reservations/create
Route::post('reservations/create', [ReservationController::class, 'bookTableByClient']);

// http://127.0.0.1:8000/api/categories/parent
Route::get('categories/parent', [CategoryController::class, 'getParentCategories']);

// http://127.0.0.1:8000/api/categories/child/dish@Ngọc Mai mai ơi, làm thêm 3 api của client à lấy tất cả món ăn, lấy tất cả món ăn mới nhất theo cột created_at và lấy chi tiết món ăn nhé
Route::get('categories/child/dish', [CategoryController::class, 'getChildCategoriesByDish']);

// http://127.0.0.1:8000/api/dishes/featured
Route::get('/dishes/featured', [DishController::class, 'getFeaturedDishes']);

// http://127.0.0.1:8000/api/dishes/category/{id}/child
Route::get('/dishes/category/{id}/child', [DishController::class, 'getDishesByChildCategory']);

// http://127.0.0.1:8000/api/dishes
Route::get('/dishes', [DishController::class, 'getAllDishes']);

// http://127.0.0.1:8000/api/dishes/latest
Route::get('/dishes/latest', [DishController::class, 'getLatestDishes']);

// http://127.0.0.1:8000/api/dishes/{id}
Route::get('/dishes/{id}', [DishController::class, 'getDishDetail']);



