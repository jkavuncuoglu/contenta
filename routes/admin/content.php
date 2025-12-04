<?php

use App\Domains\ContentManagement\Categories\Http\Controllers\Admin\CategoriesApiController;
use App\Domains\ContentManagement\Categories\Http\Controllers\Admin\CategoriesController;
use App\Domains\ContentManagement\Comments\Http\Controllers\Admin\CommentsController;
use App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsApiController;
use App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsController;
use App\Domains\ContentManagement\Tags\Http\Controllers\Admin\TagsApiController;
use App\Domains\ContentManagement\Tags\Http\Controllers\Admin\TagsController;
use App\Domains\Media\Http\Controllers\Admin\MediaController;
use Illuminate\Support\Facades\Route;

// Posts Management - View posts permission
Route::prefix('posts')->name('posts.')->middleware(['permission:view posts'])->group(function () {
    Route::get('/', [PostsController::class, 'index'])->name('index');
    Route::get('/calendar', [PostsController::class, 'calendar'])->name('calendar');
    Route::get('/{id}', [PostsController::class, 'show'])->name('show');
    Route::get('/{post}/revisions', [PostsController::class, 'revisions'])->name('revisions');
    Route::get('/{post}/revisions/{revisionId}', [PostsController::class, 'showRevision'])->name('revisions.show');
});

// Posts Management - Create posts permission
Route::prefix('posts')->name('posts.')->middleware(['permission:create posts'])->group(function () {
    Route::get('/create', [PostsController::class, 'create'])->name('create');
    Route::post('/', [PostsController::class, 'store'])->name('store');
});

// Posts Management - Update posts permission
Route::prefix('posts')->name('posts.')->middleware(['permission:update posts'])->group(function () {
    Route::get('/{id}/edit', [PostsController::class, 'edit'])->name('edit');
    Route::put('/{id}', [PostsController::class, 'update'])->name('update');
    Route::post('/{post}/revisions/{revisionId}/restore', [PostsController::class, 'restoreRevision'])->name('revisions.restore');
});

// Posts Management - Delete posts permission
Route::prefix('posts')->name('posts.')->middleware(['permission:delete posts'])->group(function () {
    Route::delete('/{id}', [PostsController::class, 'destroy'])->name('destroy');
});

// Categories Management - View categories permission
Route::prefix('categories')->name('categories.')->middleware(['permission:view categories'])->group(function () {
    Route::get('/', [CategoriesController::class, 'index'])->name('index');
});

// Categories Management - Create categories permission
Route::prefix('categories')->name('categories.')->middleware(['permission:create categories'])->group(function () {
    Route::get('/create', [CategoriesController::class, 'create'])->name('create');
    Route::post('/', [CategoriesController::class, 'store'])->name('store');
});

// Categories Management - Update categories permission
Route::prefix('categories')->name('categories.')->middleware(['permission:update categories'])->group(function () {
    Route::get('/{id}/edit', [CategoriesController::class, 'edit'])->name('edit');
    Route::put('/{id}', [CategoriesController::class, 'update'])->name('update');
});

// Categories Management - Delete categories permission
Route::prefix('categories')->name('categories.')->middleware(['permission:delete categories'])->group(function () {
    Route::delete('/{id}', [CategoriesController::class, 'destroy'])->name('destroy');
});

// Tags Management - View tags permission
Route::prefix('tags')->name('tags.')->middleware(['permission:view tags'])->group(function () {
    Route::get('/', [TagsController::class, 'index'])->name('index');
});

// Tags Management - Create tags permission
Route::prefix('tags')->name('tags.')->middleware(['permission:create tags'])->group(function () {
    Route::get('/create', [TagsController::class, 'create'])->name('create');
    Route::post('/', [TagsController::class, 'store'])->name('store');
});

// Tags Management - Update tags permission
Route::prefix('tags')->name('tags.')->middleware(['permission:update tags'])->group(function () {
    Route::get('/{id}/edit', [TagsController::class, 'edit'])->name('edit');
    Route::put('/{id}', [TagsController::class, 'update'])->name('update');
});

// Tags Management - Delete tags permission
Route::prefix('tags')->name('tags.')->middleware(['permission:delete tags'])->group(function () {
    Route::delete('/{id}', [TagsController::class, 'destroy'])->name('destroy');
});

// Comments Management - View comments permission
Route::prefix('comments')->name('comments.')->middleware(['permission:view comments'])->group(function () {
    Route::get('/', [CommentsController::class, 'index'])->name('index');
    Route::get('/{id}', [CommentsController::class, 'show'])->name('show');
});

// Comments Management - Moderate comments permission
Route::prefix('comments')->name('comments.')->middleware(['permission:moderate comments'])->group(function () {
    Route::patch('/{id}/status', [CommentsController::class, 'updateStatus'])->name('update-status');
    Route::patch('/bulk-status', [CommentsController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
});

// Comments Management - Delete comments permission
Route::prefix('comments')->name('comments.')->middleware(['permission:delete comments'])->group(function () {
    Route::delete('/{id}', [CommentsController::class, 'destroy'])->name('destroy');
});

// Media Management - View media permission
Route::prefix('media')->name('media.')->middleware(['permission:view media'])->group(function () {
    Route::get('/', [MediaController::class, 'index'])->name('index');
    Route::get('/{id}', [MediaController::class, 'show'])->name('show');
    Route::get('/collection/{collection}', [MediaController::class, 'collection'])->name('collection');
});

// Media Management - Upload media permission
Route::prefix('media')->name('media.')->middleware(['permission:upload media'])->group(function () {
    Route::post('/', [MediaController::class, 'store'])->name('store');
});

// Media Management - Delete media permission
Route::prefix('media')->name('media.')->middleware(['permission:delete media'])->group(function () {
    Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
});

// API Routes for menu item selection - View permissions required
Route::prefix('api')->name('api.')->group(function () {
    Route::middleware(['permission:view posts'])->group(function () {
        Route::get('/posts', [PostsApiController::class, 'index'])->name('posts.index');
        Route::get('/posts/calendar', [PostsApiController::class, 'calendar'])->name('posts.calendar');
        Route::get('/posts/scheduled', [PostsApiController::class, 'scheduled'])->name('posts.scheduled');
        Route::get('/posts/archived', [PostsApiController::class, 'archived'])->name('posts.archived');
    });

    Route::middleware(['permission:update posts'])->group(function () {
        Route::post('/posts/{id}/reschedule', [PostsApiController::class, 'reschedule'])->name('posts.reschedule');
        Route::post('/posts/{id}/restore', [PostsApiController::class, 'restore'])->name('posts.restore');
    });

    Route::middleware(['permission:publish posts'])->group(function () {
        Route::patch('/posts/{id}/status', [PostsApiController::class, 'changeStatus'])->name('posts.change-status');
    });

    Route::middleware(['permission:view categories'])->group(function () {
        Route::get('/categories', [CategoriesApiController::class, 'index'])->name('categories.index');
    });

    Route::middleware(['permission:view tags'])->group(function () {
        Route::get('/tags', [TagsApiController::class, 'index'])->name('tags.index');
    });
});
