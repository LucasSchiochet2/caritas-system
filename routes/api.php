<?php

use App\Http\Controllers\Api\AuthController;
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
    Route::post('logout', [AuthController::class, 'logout']);

    Route::post('parishes', [ParishController::class, 'store']);
    Route::post('users', [UserController::class, 'store']);
});
