<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BazaarCustomerController;
use App\Http\Controllers\Api\BazaarItemController;
use App\Http\Controllers\Api\ParishController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('diocese/login', [AuthController::class, 'dioceseLogin']);
Route::post('parish/login', [AuthController::class, 'parishLogin']);

Route::get('parishes', [ParishController::class, 'index']);
Route::get('roles', [RoleController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::patch('me', [UserController::class, 'updateMe']);
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('bazaar-items', [BazaarItemController::class, 'index']);
    Route::post('bazaar-items', [BazaarItemController::class, 'store']);
    Route::patch('bazaar-items/{bazaarItem}', [BazaarItemController::class, 'update']);
    Route::delete('bazaar-items/{bazaarItem}', [BazaarItemController::class, 'destroy']);
    Route::get('bazaar-customers', [BazaarCustomerController::class, 'index']);
    Route::post('bazaar-customers', [BazaarCustomerController::class, 'store']);
    Route::patch('bazaar-customers/{bazaarCustomer}', [BazaarCustomerController::class, 'update']);
    Route::get('users', [UserController::class, 'index']);
    Route::post('parishes', [ParishController::class, 'store']);
    Route::patch('parishes/{parish}', [ParishController::class, 'update']);
    Route::delete('parishes/{parish}', [ParishController::class, 'destroy']);
    Route::post('users', [UserController::class, 'store']);
    Route::patch('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);
});
