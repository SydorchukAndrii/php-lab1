<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DirectoryApiController;
use App\Http\Controllers\Api\InventoryApiController;
use App\Http\Controllers\Api\V1\ProductApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::post('login', [AuthController::class, 'login'])->name('login');
    Route::apiResource('products', ProductApiController::class)->only(['index', 'show']);

    foreach (['categories', 'suppliers', 'warehouses'] as $resource) {
        Route::get($resource, [DirectoryApiController::class, 'index'])->name($resource.'.index');
        Route::get($resource.'/{record}', [DirectoryApiController::class, 'show'])->whereNumber('record')->name($resource.'.show');
    }

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::apiResource('products', ProductApiController::class)->except(['index', 'show']);
        foreach (['categories', 'suppliers', 'warehouses'] as $resource) {
            Route::post($resource, [DirectoryApiController::class, 'store'])->name($resource.'.store');
            Route::put($resource.'/{record}', [DirectoryApiController::class, 'update'])->whereNumber('record')->name($resource.'.update');
            Route::delete($resource.'/{record}', [DirectoryApiController::class, 'destroy'])->whereNumber('record')->name($resource.'.destroy');
        }
        Route::get('stocks', [InventoryApiController::class, 'stocks'])->name('stocks.index');
        Route::get('movements', [InventoryApiController::class, 'movements'])->name('movements.index');
        Route::get('movement-items', [InventoryApiController::class, 'items'])->name('movement-items.index');
        Route::get('users', [InventoryApiController::class, 'users'])->name('users.index');
        Route::get('users/{user}/profile', [InventoryApiController::class, 'profile'])->name('users.profile');
    });
});
