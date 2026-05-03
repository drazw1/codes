<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\MedicineApiController;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\SupplierApiController;

/*
|--------------------------------------------------------------------------
| API Routes  –  prefix: /api
|--------------------------------------------------------------------------
| All responses are JSON.
| Protected routes require:
|   Authorization: Bearer <sanctum-token>
|
| PUBLIC:
|   POST  /api/login
|
| PROTECTED (auth:sanctum):
|   POST  /api/logout
|   GET   /api/user
|   CRUD  /api/medicines
|   CRUD  /api/categories
|   CRUD  /api/suppliers
*/

// ── Public: authentication ────────────────────────────────────
Route::post('/login',  [AuthApiController::class, 'login'])->name('api.login');

// ── Protected: everything else ────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthApiController::class, 'logout'])->name('api.logout');
    Route::get('/user',    [AuthApiController::class, 'user'])->name('api.user');

    /*
     * apiResource() registers only the 5 API routes (no create/edit HTML form routes):
     *   GET    /api/{resource}           index
     *   POST   /api/{resource}           store
     *   GET    /api/{resource}/{id}      show
     *   PUT    /api/{resource}/{id}      update
     *   DELETE /api/{resource}/{id}      destroy
     */
    Route::apiResource('medicines',  MedicineApiController::class);
    Route::apiResource('categories', CategoryApiController::class);
    Route::apiResource('suppliers',  SupplierApiController::class);
});
