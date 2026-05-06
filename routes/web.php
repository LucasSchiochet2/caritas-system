<?php

use App\Http\Controllers\Docs\OpenApiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'status' => 'ok',
    ]);
});

Route::get('docs/api', [OpenApiController::class, 'index'])->name('docs.openapi');
Route::get('docs/api/openapi.json', [OpenApiController::class, 'json'])->name('docs.openapi.json');
