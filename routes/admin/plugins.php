<?php

use App\Domains\Plugins\Http\Controllers\Admin\PluginsController;
use Illuminate\Support\Facades\Route;

// View plugins permission
Route::prefix('plugins')->name('plugins.')->middleware(['permission:view plugins'])->group(function () {
    Route::get('/', [PluginsController::class, 'index'])->name('index');
    Route::get('/api', [PluginsController::class, 'list'])->name('api.list');
});

// Install plugins permission
Route::prefix('plugins')->name('plugins.')->middleware(['permission:install plugins'])->group(function () {
    Route::post('/api/upload', [PluginsController::class, 'upload'])->name('api.upload');
    Route::post('/api/discover', [PluginsController::class, 'discover'])->name('api.discover');
});

// Activate/Deactivate plugins permissions
Route::prefix('plugins')->name('plugins.')->middleware(['permission:activate plugins'])->group(function () {
    Route::post('/api/{slug}/enable', [PluginsController::class, 'enable'])->name('api.enable');
});

Route::prefix('plugins')->name('plugins.')->middleware(['permission:deactivate plugins'])->group(function () {
    Route::post('/api/{slug}/disable', [PluginsController::class, 'disable'])->name('api.disable');
});

// Configure plugins permission
Route::prefix('plugins')->name('plugins.')->middleware(['permission:configure plugins'])->group(function () {
    Route::post('/api/{slug}/scan', [PluginsController::class, 'scan'])->name('api.scan');
});

// Delete plugins permission
Route::prefix('plugins')->name('plugins.')->middleware(['permission:delete plugins'])->group(function () {
    Route::delete('/api/{slug}', [PluginsController::class, 'uninstall'])->name('api.uninstall');
    Route::delete('/api/uninstalled/{folderName}', [PluginsController::class, 'deleteUninstalled'])->name('api.delete-uninstalled');
});
