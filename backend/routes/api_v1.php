<?php

/*
|--------------------------------------------------------------------------
| Mobile App API v1 — prefix /api/v1, route names "api.v1.*"
|--------------------------------------------------------------------------
|
| Protected routes require a Bearer access token carrying the "api" ability; refresh tokens
| (ability "token:refresh") are rejected everywhere except /auth/refresh.
|
*/

use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\HotelController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Services\Auth\CustomerAuthService;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
});

Route::middleware(['auth:sanctum', 'abilities:'.CustomerAuthService::ACCESS_ABILITY])->group(function (): void {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('me', [ProfileController::class, 'show'])->name('me');

    // MB-07 … MB-11 bookings of the signed-in customer
    Route::controller(BookingController::class)->prefix('bookings')->name('bookings.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->middleware('throttle:10,1')->name('store');
        Route::get('{code}', 'show')->name('show');
        Route::post('{code}/payments', 'pay')->name('payments.store');
        Route::post('{code}/cancel', 'cancel')->name('cancel');
    });
});

// MB-04 / MB-05 hotel search and rooms (public)
Route::get('hotels', [HotelController::class, 'index'])->name('hotels.index');
Route::get('hotels/{hotelId}/rooms', [HotelController::class, 'rooms'])->whereNumber('hotelId')->name('hotels.rooms');
