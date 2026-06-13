<?php

use App\Http\Controllers\Admin\Auth\LoginController;
use Backpack\CRUD\app\Http\Controllers\Auth\ForgotPasswordController;
use Backpack\CRUD\app\Http\Controllers\Auth\RegisterController;
use Backpack\CRUD\app\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Route;

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\CRUD.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => config('backpack.base.web_middleware', 'web'),
], function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('backpack.auth.login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('logout', [LoginController::class, 'logout'])->name('backpack.auth.logout');
    Route::post('logout', [LoginController::class, 'logout']);

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('backpack.auth.register');
    Route::post('register', [RegisterController::class, 'register']);

    if (config('backpack.base.setup_password_recovery_routes', true)) {
        Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('backpack.auth.password.reset');
        Route::post('password/reset', [ResetPasswordController::class, 'reset']);
        Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('backpack.auth.password.reset.token');
        Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])
            ->name('backpack.auth.password.email')
            ->middleware('backpack.throttle.password.recovery:'.config('backpack.base.password_recovery_throttle_access'));
    }
});

Route::group([
    'prefix' => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace' => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('bazaar-customer', 'BazaarCustomerCrudController');
    Route::crud('bazaar-item', 'BazaarItemCrudController');
    Route::crud('parish', 'ParishCrudController');
    Route::crud('user', 'UserCrudController');
    Route::crud('family', 'FamilyCrudController');
    Route::crud('cashbox', 'CashboxCrudController');
    Route::crud('logs-cashbox', 'LogsCashboxCrudController');
    Route::crud('parish-inventory', 'ParishInventoryCrudController');
}); // this should be the absolute last line of this file

/**
 * DO NOT ADD ANYTHING HERE.
 */
