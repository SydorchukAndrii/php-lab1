<?php

use App\Http\Controllers\DirectoryWebController;
use App\Http\Controllers\InventoryWebController;
use App\Http\Controllers\MovementItemsPageController;
use App\Http\Controllers\MovementWebController;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('ui')->name('ui.')->group(function (): void {
    Route::get('/', InventoryWebController::class)->name('home');
    Route::resource('products', ProductController::class)->only('index');
    Route::middleware('auth')->group(function (): void {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });
    Route::resource('products', ProductController::class)->only('show');

    foreach (['categories', 'suppliers', 'warehouses'] as $resource) {
        Route::resource($resource, DirectoryWebController::class)->parameters([$resource => 'record'])->only('index');
        Route::middleware(['auth', 'can:manage-directories'])->group(function () use ($resource): void {
            Route::resource($resource, DirectoryWebController::class)->parameters([$resource => 'record'])->except('index');
        });
    }
    Route::middleware(['auth', 'can:manage-stocks'])->group(function (): void {
        Route::resource('stocks', DirectoryWebController::class)->parameters(['stocks' => 'record']);
    });
    Route::middleware(['auth', 'can:manage-users'])->group(function (): void {
        Route::resource('users', DirectoryWebController::class)->parameters(['users' => 'record']);
    });
    Route::middleware(['auth', 'can:manage-movements'])->group(function (): void {
        Route::resource('movements', MovementWebController::class);
        Route::post('movements/{movement}/post', [MovementWebController::class, 'post'])->name('movements.post');
        Route::post('movements/{movement}/cancel', [MovementWebController::class, 'cancel'])->name('movements.cancel');
        Route::get('movement-items', MovementItemsPageController::class)->name('movement-items.index');
    });
});
