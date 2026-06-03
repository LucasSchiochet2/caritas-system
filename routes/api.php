<?php

use App\Http\Controllers\Api\AssistedFamilyMemberController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BazaarCustomerController;
use App\Http\Controllers\Api\BazaarItemController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\ParishController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CashboxController;
use App\Http\Controllers\Api\LogsCashboxController;
use Illuminate\Support\Facades\Route;

Route::post('diocese/login', [AuthController::class, 'dioceseLogin']);
Route::post('parish/login', [AuthController::class, 'parishLogin']);

Route::get('parishes', [ParishController::class, 'index']);
Route::get('roles', [RoleController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('me', [AuthController::class, 'me']);
    Route::patch('me', [UserController::class, 'updateMe']);
    Route::post('logout', [AuthController::class, 'logout']);

    // --- Bazar Itens ---
    Route::get('bazaar-items', [BazaarItemController::class, 'index']);
    Route::post('bazaar-items', [BazaarItemController::class, 'store']);
    Route::patch('bazaar-items/{bazaarItem}', [BazaarItemController::class, 'update']);
    Route::delete('bazaar-items/{bazaarItem}', [BazaarItemController::class, 'destroy']);

    // --- Bazar Customers ---
    Route::get('bazaar-customers', [BazaarCustomerController::class, 'index']);
    Route::post('bazaar-customers', [BazaarCustomerController::class, 'store']);
    Route::patch('bazaar-customers/{bazaarCustomer}', [BazaarCustomerController::class, 'update']);

    // --- Cashboxes ---
    Route::get('cashboxes', [CashboxController::class, 'index']);
    Route::post('cashboxes', [CashboxController::class, 'store']);
    Route::patch('cashboxes/{cashbox}', [CashboxController::class, 'update']);
    Route::delete('cashboxes/{cashbox}', [CashboxController::class, 'destroy']);

    // --- Logs Cashboxes ---
    Route::get('logs-cashboxes', [LogsCashboxController::class, 'index']);
    Route::delete('logs-cashboxes/{logsCashbox}', [LogsCashboxController::class, 'destroy']);

    // --- Families ---
    Route::get('families', [FamilyController::class, 'index']);
    Route::get('inactive-families', [FamilyController::class, 'inactivateFamilies']);
    Route::post('families', [FamilyController::class, 'store']);
    Route::get('families/{family}/financial-records', [LogsCashboxController::class, 'indexByFamily']);
    Route::get('families/{family}/assisted-family-members', [AssistedFamilyMemberController::class, 'index']);
    Route::post('families/{family}/assisted-family-members', [AssistedFamilyMemberController::class, 'store']);
    Route::patch('families/{family}', [FamilyController::class, 'update']);
    Route::patch('families/{family}/inactivate', [FamilyController::class, 'inactivate']);
    Route::patch('families/{family}/activate', [FamilyController::class, 'activate']);
    // Route::delete('families/{family}', [FamilyController::class, 'destroy']);

    // --- Assisted Family Members ---
    Route::get('assisted-family-members/search-by-cpf', [AssistedFamilyMemberController::class, 'searchByCpf']);
    Route::patch('assisted-family-members/{assistedFamilyMember}', [AssistedFamilyMemberController::class, 'update']);
    Route::delete('assisted-family-members/{assistedFamilyMember}', [AssistedFamilyMemberController::class, 'destroy']);

    // --- Users ---
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::patch('users/{user}', [UserController::class, 'update']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);

    // --- Parishes ---
    Route::post('parishes', [ParishController::class, 'store']);
    Route::patch('parishes/{parish}', [ParishController::class, 'update']);
    Route::delete('parishes/{parish}', [ParishController::class, 'destroy']);
    Route::patch('parishes/{parish}/activate', [ParishController::class, 'activate']);
    Route::get('inactive-parishes', [ParishController::class, 'inactive_parishes']);
});
