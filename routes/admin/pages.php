<?php

use App\Domains\ContentManagement\Pages\Http\Controllers\Admin\PagesController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'pages',
    'as' => 'pages.',
], function () {
    // API Routes
    Route::get('/api', [PagesController::class, 'api'])->name('api');

    // Page Management Routes
    Route::get('/', [PagesController::class, 'index'])->name('index');
    Route::get('/create', [PagesController::class, 'create'])->name('create');
    Route::get('/{page}/edit', [PagesController::class, 'edit'])->name('edit');
    Route::post('/', [PagesController::class, 'store'])->name('store');
    Route::put('/{page}', [PagesController::class, 'update'])->name('update');
    Route::delete('/{page}', [PagesController::class, 'destroy'])->name('destroy');
    Route::get('/{page}', [PagesController::class, 'show'])->name('show');

    // Page Actions
    Route::post('/{page}/publish', [PagesController::class, 'publish'])->name('publish');
    Route::post('/{page}/unpublish', [PagesController::class, 'unpublish'])->name('unpublish');
    Route::post('/{page}/duplicate', [PagesController::class, 'duplicate'])->name('duplicate');
    Route::post('/{page}/preview', [PagesController::class, 'preview'])->name('preview');
    Route::post('/validate', [PagesController::class, 'validate'])->name('validate');

    // Revision history routes
    Route::get('/{page}/revisions', [PagesController::class, 'revisions'])->name('revisions');
    Route::get('/{page}/revisions/{revisionId}', [PagesController::class, 'showRevision'])->name('revisions.show');
    Route::post('/{page}/revisions/{revisionId}/restore', [PagesController::class, 'restoreRevision'])->name('revisions.restore');
});
