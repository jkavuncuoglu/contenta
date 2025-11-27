<?php

use App\Domains\Themes\Http\Controllers\Admin\ThemesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::prefix('themes')->name('themes.')->group(function () {
        Route::get('/', [ThemesController::class, 'index'])->name('index');
        Route::post('/{id}/activate', [ThemesController::class, 'activate'])->name('activate');
        Route::post('/install', [ThemesController::class, 'install'])->name('install');
        Route::delete('/{id}', [ThemesController::class, 'uninstall'])->name('uninstall');
        Route::post('/scan', [ThemesController::class, 'scan'])->name('scan');
    });
});
