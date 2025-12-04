<?php

use App\Domains\ContentManagement\Pages\Http\Controllers\Admin\PagesController;
use Illuminate\Support\Facades\Route;

// View pages permission
Route::group([
    'prefix' => 'pages',
    'as' => 'pages.',
    'middleware' => ['permission:view pages'],
], function () {
    // API Routes
    Route::get('/api', [PagesController::class, 'api'])->name('api');
    Route::get('/', [PagesController::class, 'index'])->name('index');
    Route::get('/{page}', [PagesController::class, 'show'])->name('show');
    Route::get('/{page}/revisions', [PagesController::class, 'revisions'])->name('revisions');
    Route::get('/{page}/revisions/{revisionId}', [PagesController::class, 'showRevision'])->name('revisions.show');
});

// Create pages permission
Route::group([
    'prefix' => 'pages',
    'as' => 'pages.',
    'middleware' => ['permission:create pages'],
], function () {
    Route::get('/create', [PagesController::class, 'create'])->name('create');
    Route::post('/', [PagesController::class, 'store'])->name('store');
    Route::post('/validate', [PagesController::class, 'validate'])->name('validate');
});

// Update pages permission
Route::group([
    'prefix' => 'pages',
    'as' => 'pages.',
    'middleware' => ['permission:update pages'],
], function () {
    Route::get('/{page}/edit', [PagesController::class, 'edit'])->name('edit');
    Route::put('/{page}', [PagesController::class, 'update'])->name('update');
    Route::post('/{page}/preview', [PagesController::class, 'preview'])->name('preview');
    Route::post('/{page}/revisions/{revisionId}/restore', [PagesController::class, 'restoreRevision'])->name('revisions.restore');
});

// Delete pages permission
Route::group([
    'prefix' => 'pages',
    'as' => 'pages.',
    'middleware' => ['permission:delete pages'],
], function () {
    Route::delete('/{page}', [PagesController::class, 'destroy'])->name('destroy');
});

// Publish pages permission
Route::group([
    'prefix' => 'pages',
    'as' => 'pages.',
    'middleware' => ['permission:publish pages'],
], function () {
    Route::post('/{page}/publish', [PagesController::class, 'publish'])->name('publish');
    Route::post('/{page}/unpublish', [PagesController::class, 'unpublish'])->name('unpublish');
    Route::post('/{page}/duplicate', [PagesController::class, 'duplicate'])->name('duplicate');
});
