<?php

use App\Domains\Themes\Http\Controllers\Admin\ThemesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    // View themes permission
    Route::prefix('themes')->name('themes.')->middleware(['permission:view themes'])->group(function () {
        Route::get('/', [ThemesController::class, 'index'])->name('index');
    });

    // Activate themes permission
    Route::prefix('themes')->name('themes.')->middleware(['permission:activate themes'])->group(function () {
        Route::post('/{id}/activate', [ThemesController::class, 'activate'])->name('activate');
    });

    // Install themes permission
    Route::prefix('themes')->name('themes.')->middleware(['permission:install themes'])->group(function () {
        Route::post('/install', [ThemesController::class, 'install'])->name('install');
        Route::post('/scan', [ThemesController::class, 'scan'])->name('scan');
    });

    // Delete themes permission
    Route::prefix('themes')->name('themes.')->middleware(['permission:delete themes'])->group(function () {
        Route::delete('/{id}', [ThemesController::class, 'uninstall'])->name('uninstall');
    });
});
