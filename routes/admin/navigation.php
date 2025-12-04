<?php

use App\Domains\Navigation\Http\Controllers\Admin\MenuController;
use App\Domains\Navigation\Http\Controllers\Admin\MenuItemController;
use Illuminate\Support\Facades\Route;

// View menus permission
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:view menus'],
], function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::get('/{menu}/edit', [MenuController::class, 'edit'])->name('edit');
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/{menu}/items', [MenuItemController::class, 'index'])->name('items.index');
        Route::get('/{menu}/export', [MenuController::class, 'export'])->name('export');
    });
});

// Create menus permission
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:create menus'],
], function () {
    Route::get('/create', [MenuController::class, 'create'])->name('create');
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/', [MenuController::class, 'store'])->name('store');
        Route::post('/{menu}/duplicate', [MenuController::class, 'duplicate'])->name('duplicate');
        Route::post('/import', [MenuController::class, 'import'])->name('import');
    });
});

// Update menus permission
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:update menus'],
], function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::put('/{menu}', [MenuController::class, 'update'])->name('update');
    });
});

// Delete menus permission
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:delete menus'],
], function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::delete('/{menu}', [MenuController::class, 'destroy'])->name('destroy');
    });
});

// View menu items permission (combined with view menus, but separate for granularity)
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:view menu items'],
], function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/{menu}/items', [MenuItemController::class, 'index'])->name('items.index');
    });
});

// Create menu items permission
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:create menu items'],
], function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/{menu}/items', [MenuItemController::class, 'store'])->name('items.store');
        Route::post('/{menu}/items/bulk-create', [MenuItemController::class, 'bulkCreate'])->name('items.bulk-create');
    });
});

// Update menu items permission
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:update menu items'],
], function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::put('/{menu}/items/{item}', [MenuItemController::class, 'update'])->name('items.update');
    });
});

// Delete menu items permission
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:delete menu items'],
], function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::delete('/{menu}/items/{item}', [MenuItemController::class, 'destroy'])->name('items.destroy');
    });
});

// Reorder menu items permission
Route::group([
    'prefix' => 'menus',
    'as' => 'menus.',
    'middleware' => ['permission:reorder menu items'],
], function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/{menu}/items/reorder', [MenuItemController::class, 'reorder'])->name('items.reorder');
    });
});
