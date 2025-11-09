<?php

use App\Domains\ContentManagement\Categories\Http\Controllers\Admin\CategoriesController;
use App\Domains\ContentManagement\Categories\Http\Controllers\Admin\CategoriesApiController;
use App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsController;
use App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsApiController;
use App\Domains\ContentManagement\Tags\Http\Controllers\Admin\TagsController;
use App\Domains\ContentManagement\Tags\Http\Controllers\Admin\TagsApiController;
use App\Domains\ContentManagement\Comments\Http\Controllers\Admin\CommentsController;
use App\Domains\Media\Http\Controllers\Admin\MediaController;
use Illuminate\Support\Facades\Route;

// Posts Management
Route::prefix('posts')->name('posts.')->group(function () {
    Route::get('/', [PostsController::class, 'index'])->name('index');
    Route::get('/create', [PostsController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [PostsController::class, 'edit'])->name('edit');
    Route::get('/{id}', [PostsController::class, 'show'])->name('show');
});

// Categories Management
Route::prefix('categories')->name('categories.')->group(function () {
    Route::get('/', [CategoriesController::class, 'index'])->name('index');
    Route::get('/create', [CategoriesController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [CategoriesController::class, 'edit'])->name('edit');
});

// Tags Management
Route::prefix('tags')->name('tags.')->group(function () {
    Route::get('/', [TagsController::class, 'index'])->name('index');
    Route::get('/create', [TagsController::class, 'create'])->name('create');
    Route::get('/{id}/edit', [TagsController::class, 'edit'])->name('edit');
});

// Comments Management
Route::prefix('comments')->name('comments.')->group(function () {
    Route::get('/', [CommentsController::class, 'index'])->name('index');
    Route::get('/{id}', [CommentsController::class, 'show'])->name('show');
    Route::patch('/{id}/status', [CommentsController::class, 'updateStatus'])->name('update-status');
    Route::patch('/bulk-status', [CommentsController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
    Route::delete('/{id}', [CommentsController::class, 'destroy'])->name('destroy');
});

// Media Management
Route::prefix('media')->name('media.')->group(function () {
    Route::get('/', [MediaController::class, 'index'])->name('index');
    Route::post('/', [MediaController::class, 'store'])->name('store');
    Route::get('/{id}', [MediaController::class, 'show'])->name('show');
    Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
    Route::get('/collection/{collection}', [MediaController::class, 'collection'])->name('collection');
});

// API Routes for menu item selection
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/posts', [PostsApiController::class, 'index'])->name('posts.index');
    Route::get('/categories', [CategoriesApiController::class, 'index'])->name('categories.index');
    Route::get('/tags', [TagsApiController::class, 'index'])->name('tags.index');
});
