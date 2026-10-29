<?php

declare(strict_types=1);

use App\Http\Controllers\MovementController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'OK', 'timestamp' => now()->toIso8601String()]))->name('health');
Route::get('/system-info', fn () => response()->json(['application' => 'Warehouse Inventory', 'framework' => 'Laravel']))->name('system-info');

Route::prefix('products')->name('products.')->group(function (): void {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/', [ProductController::class, 'store'])->middleware('scanner.access')->name('store');
    Route::get('/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('show');
});

Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
