<?php

use App\Http\Controllers\Api\AssistedFamilyMemberController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BasketDeliveryController;
use App\Http\Controllers\Api\BasketTemplateController;
use App\Http\Controllers\Api\BazaarCustomerController;
use App\Http\Controllers\Api\BazaarItemController;
use App\Http\Controllers\Api\CashboxController;
use App\Http\Controllers\Api\FamilyController;
use App\Http\Controllers\Api\LogsCashboxController;
use App\Http\Controllers\Api\HomeVisitController;
use App\Http\Controllers\Api\ParishController;
use App\Http\Controllers\Api\ParishInventoryController;
use App\Http\Controllers\Api\ParishInventoryItemController;
use App\Http\Controllers\Api\ParishInventoryRepasseController;
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

    // --- Parish Inventories ---
    Route::get('parish-inventories', [ParishInventoryController::class, 'index']);
    Route::post('parish-inventories', [ParishInventoryController::class, 'store']);
    Route::patch('parish-inventories/{parishInventory}', [ParishInventoryController::class, 'update']);
    Route::delete('parish-inventories/{parishInventory}', [ParishInventoryController::class, 'destroy']);

    // --- Parish Inventory Items ---
    Route::get('parish-inventory-items', [ParishInventoryItemController::class, 'index']);
    Route::get('parish-inventory-items/{parishId}', [ParishInventoryItemController::class, 'indexbyParish']);
    Route::post('parish-inventory-items', [ParishInventoryItemController::class, 'store']);
    Route::post('parish-inventory-items/{parishInventoryItem}/quantities', [ParishInventoryItemController::class, 'addQuantity']);
    Route::patch('parish-inventory-items/{parishInventoryItem}', [ParishInventoryItemController::class, 'update']);
    Route::delete('parish-inventory-items/{parishInventoryItem}', [ParishInventoryItemController::class, 'destroy']);
    Route::get('expired-items', [ParishInventoryItemController::class, 'expired_items']);
    Route::get('low-stock-items', [ParishInventoryItemController::class, 'low_stock_items']);
    Route::get('valid-until-this-week', [ParishInventoryItemController::class, 'valid_until_this_week']);

    // --- Parish Inventory Repasses ---
    Route::get('parish-inventory-repasses', [ParishInventoryRepasseController::class, 'index']);
    Route::post('parish-inventory-repasses', [ParishInventoryRepasseController::class, 'store']);
    Route::get('parish-inventory-repasses/{parishInventoryRepasse}', [ParishInventoryRepasseController::class, 'show']);

    // --- Basket Templates and Deliveries ---
    Route::get('basket-templates', [BasketTemplateController::class, 'index']);
    Route::post('basket-templates', [BasketTemplateController::class, 'store']);
    Route::get('basket-templates/{basketTemplate}', [BasketTemplateController::class, 'show']);
    Route::patch('basket-templates/{basketTemplate}', [BasketTemplateController::class, 'update']);
    Route::delete('basket-templates/{basketTemplate}', [BasketTemplateController::class, 'destroy']);

    Route::get('basket-deliveries', [BasketDeliveryController::class, 'index']);
    Route::post('basket-deliveries', [BasketDeliveryController::class, 'store']);
    Route::get('basket-deliveries/{basketDelivery}', [BasketDeliveryController::class, 'show']);

    // --- Families ---
    Route::get('families', [FamilyController::class, 'index']);
    Route::get('inactive-families', [FamilyController::class, 'inactivateFamilies']);
    Route::post('families', [FamilyController::class, 'store']);
    Route::get('families/{family}/basket-deliveries', [BasketDeliveryController::class, 'familyIndex']);
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

    // --- Home Visits ---
    Route::get('home-visits', [HomeVisitController::class, 'index']);
    Route::get('home-visits/history', [HomeVisitController::class, 'history']);
    Route::get('families/{family}/home-visits', [HomeVisitController::class, 'indexByFamily']);
    Route::post('families/{family}/home-visits', [HomeVisitController::class, 'store']);
    Route::patch('home-visits/{homeVisit}', [HomeVisitController::class, 'update']);
    Route::delete('home-visits/{homeVisit}', [HomeVisitController::class, 'destroy']);
    Route::patch('home-visits/{homeVisit}/reschedule', [HomeVisitController::class, 'reschedule']);
    Route::patch('home-visits/{homeVisit}/cancel', [HomeVisitController::class, 'cancel']);
    Route::patch('home-visits/{homeVisit}/visit-record', [HomeVisitController::class, 'visit_record']);

    // --- Users ---
    Route::get('users', [UserController::class, 'index']);
    Route::get('inactive-users', [UserController::class, 'inactiveUsers']);
    Route::post('users', [UserController::class, 'store']);
    Route::patch('users/{user}', [UserController::class, 'update']);
    Route::patch('users/{user}/inactivate', [UserController::class, 'inactivate']);
    Route::patch('users/{user}/activate', [UserController::class, 'activate']);
    Route::delete('users/{user}', [UserController::class, 'destroy']);

    // --- Parishes ---
    Route::post('parishes', [ParishController::class, 'store']);
    Route::patch('parishes/{parish}', [ParishController::class, 'update']);
    Route::delete('parishes/{parish}', [ParishController::class, 'destroy']);
    Route::patch('parishes/{parish}/activate', [ParishController::class, 'activate']);
    Route::get('inactive-parishes', [ParishController::class, 'inactive_parishes']);
});
