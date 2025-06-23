<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\Reservation\ReservationController;

Route::post('reservations/create', [ReservationController::class, 'bookTableByClient']);
