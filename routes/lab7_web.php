<?php

// Окремий HTML-інтерфейс для лабораторної №7; існуючі JSON-маршрути №6 не змінюємо.
use App\Http\Controllers\DirectoryWebController;
use App\Http\Controllers\InventoryWebController;
use App\Http\Controllers\MovementWebController;
use App\Http\Controllers\ProductWebController;
use App\Models\MovementItem;
use Illuminate\Support\Facades\Route;

Route::prefix('ui')->name('ui.')->group(function (): void {
    Route::get('/', InventoryWebController::class)->name('home');
    Route::resource('products', ProductWebController::class);
    foreach (['categories', 'suppliers', 'warehouses', 'stocks', 'users'] as $resource) {
        Route::resource($resource, DirectoryWebController::class)
            ->parameters([$resource => 'record']);
    }
    Route::resource('movements', MovementWebController::class);
    Route::post('movements/{movement}/post', [MovementWebController::class, 'post'])->name('movements.post');
    Route::post('movements/{movement}/cancel', [MovementWebController::class, 'cancel'])->name('movements.cancel');
    Route::get('movement-items', fn () => view('ui.movement-items', [
        'items' => MovementItem::with(['movement', 'product'])->latest('id')->paginate(15),
    ]))->name('movement-items.index');
});
