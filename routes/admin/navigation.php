<?php

use App\Domains\Navigation\Http\Controllers\Admin\MenuController;
use App\Domains\Navigation\Http\Controllers\Admin\MenuItemController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
], function () {
    // Frontend Views
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::get('/create', [MenuController::class, 'create'])->name('create');
    Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');

    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        // Menu CRUD
        Route::post('/', [MenuController::class, 'store'])->name('store');
        Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');

        // Menu Actions
        Route::post('/{menu}/duplicate', [MenuController::class, 'duplicate'])->name('duplicate');
        Route::get('/{menu}/export', [MenuController::class, 'export'])->name('export');
        Route::post('/import', [MenuController::class, 'import'])->name('import');

        // Menu Items
        Route::get('/{menu}/items', [MenuItemController::class, 'index'])->name('items.index');
        Route::post('/{menu}/items', [MenuItemController::class, 'store'])->name('items.store');
        Route::put('/{menu}/items/{item}', [MenuItemController::class, 'update'])->name('items.update');
        Route::delete('/{menu}/items/{item}', [MenuItemController::class, 'destroy'])->name('items.destroy');
        Route::post('/{menu}/items/reorder', [MenuItemController::class, 'reorder'])->name('items.reorder');
        Route::post('/{menu}/items/bulk-create', [MenuItemController::class, 'bulkCreate'])->name('items.bulk-create');
    });
});
