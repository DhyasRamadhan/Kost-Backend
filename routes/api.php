<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\TenantUpdateRequestController;
use App\Http\Controllers\API\ContractController;
use App\Http\Controllers\API\TenantController;
use App\Http\Controllers\API\OwnerVerificationController;

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Owner Verification
    Route::prefix('owners')->group(function () {
        Route::get('/pending', [OwnerVerificationController::class, 'pending']);
        Route::post('/{id}/approve', [OwnerVerificationController::class, 'approve']);
        Route::post('/{id}/reject', [OwnerVerificationController::class, 'reject']);
    });

    // Tenant Update Requests
    Route::prefix('tenant')->group(function () {
        Route::post('/update-request', [TenantUpdateRequestController::class, 'store']);

        Route::middleware('is_owner')->group(function () {
            Route::get('/update-requests', [TenantUpdateRequestController::class, 'index']);
            Route::post('/update-requests/{id}/approve', [TenantUpdateRequestController::class, 'approve']);
            Route::post('/update-requests/{id}/reject', [TenantUpdateRequestController::class, 'reject']);
        });
    });

    // Resources
    Route::apiResource('rooms', RoomController::class);
    Route::apiResource('tenants', TenantController::class);
    Route::apiResource('contracts', ContractController::class)->only(['index', 'store', 'show', 'destroy']);
});
