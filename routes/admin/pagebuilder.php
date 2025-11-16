<?php

use App\Domains\PageBuilder\Http\Controllers\Admin\BlockController;
use App\Domains\PageBuilder\Http\Controllers\Admin\LayoutController;
use App\Domains\PageBuilder\Http\Controllers\Admin\PageBuilderController;
use App\Domains\PageBuilder\Http\Controllers\Admin\PageController;
use App\Domains\PageBuilder\Http\Controllers\Admin\PageRevisionController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'page-builder',
    'as' => 'page-builder.',
], function () {
    // Frontend Views
    Route::get('/', [PageBuilderController::class, 'index'])->name('index');
    Route::get('/create', [PageBuilderController::class, 'create'])->name('create');
    Route::get('/{page}/edit', [PageBuilderController::class, 'edit'])->name('edit');

    // Layouts Frontend
    Route::get('/layouts', [PageBuilderController::class, 'layouts'])->name('layouts.index');
    Route::get('/layouts/create', [PageBuilderController::class, 'createLayout'])->name('layouts.create');
    Route::get('/layouts/{layout}/edit', [PageBuilderController::class, 'editLayout'])->name('layouts.edit');

    // Blocks Frontend
    Route::get('/blocks', [PageBuilderController::class, 'blocks'])->name('blocks.index');
    Route::get('/blocks/create', [PageBuilderController::class, 'createBlock'])->name('blocks.create');
    Route::get('/blocks/{block}/edit', [PageBuilderController::class, 'editBlock'])->name('blocks.edit');

    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        // Pages
        Route::apiResource('pages', PageController::class);
        Route::post('pages/{page}/publish', [PageController::class, 'publish'])->name('pages.publish');
        Route::post('pages/{page}/unpublish', [PageController::class, 'unpublish'])->name('pages.unpublish');
        Route::post('pages/{page}/duplicate', [PageController::class, 'duplicate'])->name('pages.duplicate');
        Route::get('pages/{page}/preview', [PageController::class, 'preview'])->name('pages.preview');

        // Page Revisions
        Route::get('pages/{page}/revisions', [PageRevisionController::class, 'index'])->name('pages.revisions.index');
        Route::get('pages/{page}/revisions/{revision}', [PageRevisionController::class, 'show'])->name('pages.revisions.show');
        Route::post('pages/{page}/revisions/{revision}/restore', [PageRevisionController::class, 'restore'])->name('pages.revisions.restore');

        // Layouts
        Route::apiResource('layouts', LayoutController::class);

        // Blocks
        Route::apiResource('blocks', BlockController::class);
        Route::get('blocks-categories', [BlockController::class, 'categories'])->name('blocks.categories');
        Route::post('blocks/{block}/validate-config', [BlockController::class, 'validateConfig'])->name('blocks.validate-config');
    });
});
