<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RoomController;
use App\Http\Controllers\API\TenantUpdateRequestController;
use App\Http\Controllers\API\ContractController;
use App\Http\Controllers\API\TenantController;

// Delete if done
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function () {
    return response()->json(['message' => 'API OK']);
});

// Authentication
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

// Tenant Update Requests
Route::middleware('auth:sanctum')->post('/tenant/update-request', [TenantUpdateRequestController::class, 'store']);
Route::middleware(['auth:sanctum', 'is_owner'])->get('/tenant/update-requests', [TenantUpdateRequestController::class, 'index']);
Route::middleware(['auth:sanctum', 'is_owner'])->post('/tenant/update-requests/{id}/approve', [TenantUpdateRequestController::class, 'approve']);
Route::middleware(['auth:sanctum', 'is_owner'])->post('/tenant/update-requests/{id}/reject', [TenantUpdateRequestController::class, 'reject']);

// Contract Management
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('contracts', ContractController::class)->only(['index', 'store', 'show', 'destroy']);
});

// Room Management
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('rooms', RoomController::class);
});

// Tenant Management
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('tenants', TenantController::class);
});
