<?php

use App\Domains\ContentManagement\Categories\Http\Controllers\Admin\CategoriesController;
use App\Domains\ContentManagement\Pages\Http\Controllers\Admin\PagesController;
use App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsController;
use App\Domains\ContentManagement\Tags\Http\Controllers\Admin\TagsController;
use Illuminate\Support\Facades\Route;

// Pages Management
Route::prefix('pages')->name('pages.')->group(function () {
    Route::get('/', [PagesController::class, 'index'])->name('index');
    Route::get('/create', [PagesController::class, 'create'])->name('create');
    Route::post('/', [PagesController::class, 'store'])->name('store');
    Route::get('/{page}/edit', [PagesController::class, 'edit'])->name('edit');
    Route::put('/{page}', [PagesController::class, 'update'])->name('update');
    Route::delete('/{page}', [PagesController::class, 'destroy'])->name('destroy');
});

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
