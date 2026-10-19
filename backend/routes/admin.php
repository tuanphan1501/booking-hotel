<?php

/*
|--------------------------------------------------------------------------
| Web Admin — prefix /admin, route names "admin.*"
|--------------------------------------------------------------------------
|
| Server-rendered Blade pages using the session guard "admin".
|
*/

use App\Http\Controllers\Admin\Auth\GoogleLoginController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:20,1')->name('login.store');
    Route::get('login/verify', [LoginController::class, 'verifyForm'])->name('login.verify');
    Route::post('login/verify', [LoginController::class, 'verify'])->middleware('throttle:20,1')->name('login.verify.store');
    Route::post('login/resend', [LoginController::class, 'resend'])->middleware('throttle:6,1')->name('login.resend');
    Route::post('login/cancel', [LoginController::class, 'cancel'])->name('login.cancel');
    Route::get('auth/google', [GoogleLoginController::class, 'redirect'])->name('login.google');
    Route::get('auth/google/callback', [GoogleLoginController::class, 'callback'])->name('login.google.callback');
    Route::post('language', [LoginController::class, 'switchLanguage'])->name('language');
});

Route::middleware('auth:admin')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // AD-03 / AD-04 Bookings
    Route::controller(BookingController::class)->prefix('bookings')->name('bookings.')->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('{code}', 'show')->name('show');
        Route::post('{code}/check-in', 'checkIn')->name('check-in');
        Route::post('{code}/check-out', 'checkOut')->name('check-out');
        Route::post('{code}/cancel', 'cancel')->name('cancel');
        Route::post('{code}/payments', 'recordPayment')->name('payments.store');
        Route::put('{code}/note', 'updateNote')->name('note.update');
    });

    // AD-19 Personal settings (own profile and picture)
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function (): void {
        Route::get('/', 'edit')->name('edit');
        Route::put('/', 'update')->name('update');
        Route::post('avatar', 'updateAvatar')->name('avatar.update');
        Route::delete('avatar', 'destroyAvatar')->name('avatar.destroy');
    });
});
