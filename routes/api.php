<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\TableController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ItemController;
use App\Http\Controllers\Api\Admin\ItemVariantController;
use App\Http\Controllers\Api\Admin\TableController as AdminTableController;
use App\Http\Controllers\Api\Admin\UserController;

// ==========================================
// Routes عامة - خاصة بالزبون، بدون تسجيل دخول
// ==========================================
Route::get('/menu', [MenuController::class, 'index']);
Route::get('/table/{qrToken}', [TableController::class, 'show']);
Route::post('/orders', [OrderController::class, 'store']);

// تسجيل الدخول (عام، أي حدا يقدر يوصله لتسجيل الدخول)
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// Routes محمية - خاصة بالموظفين (لازم تسجيل دخول)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::patch('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
});

// ==========================================
// Routes خاصة بالمسؤولين (لازم تسجيل دخول + دور admin)
// ==========================================

Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('items', ItemController::class);
    Route::apiResource('item-variants', ItemVariantController::class);
    Route::apiResource('tables', AdminTableController::class);
    Route::post('tables/{table}/regenerate-qr', [AdminTableController::class, 'regenerateQrToken']);
    Route::apiResource('users', UserController::class)->only(['index', 'store', 'show', 'destroy']);
});
