<?php

use App\Http\Controllers\Client\Cart\CartController;
use App\Http\Controllers\Client\Category\CategoryController;
use App\Http\Controllers\Client\Dish\DishController;
use App\Http\Controllers\Client\Payment\PaymentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\Reservation\ReservationController;

// http://127.0.0.1:8000/api/reservations/create
Route::post('reservations/create', [ReservationController::class, 'bookTableByClient']);

// http://127.0.0.1:8000/api/categories/parent
Route::get('categories/parent', [CategoryController::class, 'getParentCategories']);

// http://127.0.0.1:8000/api/categories/child/dish
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

// http://127.0.0.1:8000/api/carts
Route::get('/carts', [CartController::class, 'getCart']);

//  http://127.0.0.1:8000/api/carts/add
Route::post('/carts/add', [CartController::class, 'addToCart']);

// http://127.0.0.1:8000/api/carts/update
Route::post('carts/update/', action: [CartController::class, 'updateCartItems']);

// http://127.0.0.1:8000/api/carts/remove
Route::post('carts/remove', [CartController::class, 'removeCartItems']);

// http://127.0.0.1:8000/api/carts/clear
Route::delete('carts/clear', [CartController::class, 'clearCart']);

 Route::post('orders/{id}/pay', [PaymentController::class, 'handlePayment']);
 Route::get('/vnpay-return', [PaymentController::class, 'vnpayReturn']);
Route::get('/momo-return', [PaymentController::class, 'momoReturn']);

    // Route::get('orders/{id}/vnpay-url', [PaymentController::class, 'createVnpayUrl']);

    // Route::get('orders/{id}/momo-url', [PaymentController::class, 'createMomoUrl']);


