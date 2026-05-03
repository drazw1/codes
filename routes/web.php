<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\SupplierController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| All routes here require authentication via the 'auth' middleware
| provided by Laravel Breeze.
*/

// Public landing → redirect to dashboard or login
Route::get('/', fn() => redirect()->route('dashboard'));

// ── Authenticated routes ──────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
         ->name('dashboard');

    // Full CRUD resource routes (generates all 7 RESTful routes each)
    Route::resource('categories', CategoryController::class);
    Route::resource('medicines',  MedicineController::class);
    Route::resource('suppliers',  SupplierController::class);
});

// Breeze auth routes (login, register, password reset, etc.)
require __DIR__ . '/auth.php';
