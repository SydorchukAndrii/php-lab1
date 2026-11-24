<?php

declare(strict_types=1);

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\MovementItemController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SentryDiagnosticController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SystemInfoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthCheckController::class)->name('health');
Route::get('/system-info', SystemInfoController::class)->name('system-info');
// Laboratory diagnostic, unavailable in production and protected by admin authorization.
Route::get('/lab9/sentry-test', SentryDiagnosticController::class)
    ->middleware(['auth', 'can:manage-users'])->name('lab9.sentry-test');

Route::prefix('products')->name('products.')->group(function (): void {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::post('/', [ProductController::class, 'store'])->middleware('scanner.access')->name('store');
    Route::get('/{id}', [ProductController::class, 'show'])->whereNumber('id')->name('show');
    Route::put('/{id}', [ProductController::class, 'update'])->whereNumber('id')->middleware('scanner.access')->name('update');
});
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->middleware('scanner.access')->name('categories.store');
Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('scanner.access')->name('suppliers.store');
Route::get('/warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
Route::post('/warehouses', [WarehouseController::class, 'store'])->middleware('scanner.access')->name('warehouses.store');
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->middleware('scanner.access')->name('users.store');
Route::get('/users/{id}/profile', [UserProfileController::class, 'show'])->whereNumber('id')->name('users.profile');
Route::put('/users/{id}/profile', [UserProfileController::class, 'update'])->whereNumber('id')->middleware('scanner.access')->name('users.profile.update');
Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
Route::get('/stocks/low', [StockController::class, 'low'])->name('stocks.low');
Route::get('/movements', [MovementController::class, 'index'])->name('movements.index');
Route::get('/movements/{id}', [MovementController::class, 'show'])->whereNumber('id')->name('movements.show');
Route::post('/movements', [MovementController::class, 'store'])->middleware('scanner.access')->name('movements.store');
Route::post('/movements/{id}/post', [MovementController::class, 'post'])->whereNumber('id')->middleware('scanner.access')->name('movements.post');
Route::post('/movements/{id}/cancel', [MovementController::class, 'cancel'])->whereNumber('id')->middleware('scanner.access')->name('movements.cancel');
Route::get('/movement-items', [MovementItemController::class, 'index'])->name('movement-items.index');

require __DIR__.'/lab8_auth.php';
require __DIR__.'/lab7_web.php';
