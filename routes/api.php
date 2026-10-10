<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TiffinController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::get('/tiffins', [TiffinController::class, 'index']);
Route::get('/tiffins/{id}', [TiffinController::class, 'show']);

// Protected routes (Both Admin & Customer)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Customer Panel routes
    Route::prefix('customer')->group(function () {
        Route::get('/profile', [CustomerController::class, 'getProfile']);
        Route::put('/profile', [CustomerController::class, 'updateProfile']);
        
        Route::apiResource('leaves', CustomerController::class)->only(['index', 'store', 'destroy']);
        
        Route::get('/logs', [CustomerController::class, 'getLogs']);
        Route::get('/bills', [CustomerController::class, 'getBills']);
    });

    // Admin Panel routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::apiResource('customers', AdminController::class);
        Route::apiResource('tiffins', TiffinController::class)->except(['index', 'show']);
        
        Route::get('/daily-logs', [AdminController::class, 'getLogsByDate']);
        Route::post('/daily-logs/bulk-update', [AdminController::class, 'bulkUpdateLogs']);
        
        Route::post('/billing/generate', [AdminController::class, 'generateBills']);
    });
});
