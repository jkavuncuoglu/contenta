<?php

use App\Domains\PageBuilder\Http\Controllers\Admin\PageController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'pages',
    'as' => 'pages.',
], function () {
    // API Routes
    Route::get('/api', [PageController::class, 'api'])->name('api');

    // Page Management Routes
    Route::get('/', [PageController::class, 'index'])->name('index');
    Route::get('/create', [PageController::class, 'create'])->name('create');
    Route::get('/{page}/edit', [PageController::class, 'edit'])->name('edit');
    Route::post('/', [PageController::class, 'store'])->name('store');
    Route::put('/{page}', [PageController::class, 'update'])->name('update');
    Route::delete('/{page}', [PageController::class, 'destroy'])->name('destroy');

    // Page Actions
    Route::post('/{page}/publish', [PageController::class, 'publish'])->name('publish');
    Route::post('/{page}/unpublish', [PageController::class, 'unpublish'])->name('unpublish');
    Route::post('/{page}/duplicate', [PageController::class, 'duplicate'])->name('duplicate');
    Route::post('/{page}/preview', [PageController::class, 'preview'])->name('preview');
    Route::post('/validate', [PageController::class, 'validate'])->name('validate');
});
