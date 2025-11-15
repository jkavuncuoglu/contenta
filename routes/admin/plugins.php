<?php

use App\Domains\Plugins\Http\Controllers\Admin\PluginsController;
use Illuminate\Support\Facades\Route;

Route::prefix('plugins')->name('plugins.')->group(function () {
    // UI routes
    Route::get('/', [PluginsController::class, 'index'])->name('index');

    // API routes
    Route::get('/api', [PluginsController::class, 'list'])->name('api.list');
    Route::post('/api/upload', [PluginsController::class, 'upload'])->name('api.upload');
    Route::post('/api/{slug}/enable', [PluginsController::class, 'enable'])->name('api.enable');
    Route::post('/api/{slug}/disable', [PluginsController::class, 'disable'])->name('api.disable');
    Route::post('/api/{slug}/scan', [PluginsController::class, 'scan'])->name('api.scan');
    Route::delete('/api/{slug}', [PluginsController::class, 'uninstall'])->name('api.uninstall');
    Route::post('/api/discover', [PluginsController::class, 'discover'])->name('api.discover');
    Route::delete('/api/uninstalled/{folderName}', [PluginsController::class, 'deleteUninstalled'])->name('api.delete-uninstalled');
});
