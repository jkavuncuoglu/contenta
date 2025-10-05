<?php

use App\Domains\ContentManagement\Http\Controllers\Admin\CategoriesController;
use App\Domains\ContentManagement\Http\Controllers\Admin\TagsController;
use App\Domains\Settings\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Admin root redirect to dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    });

    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('admin/Dashboard');
    })->name('dashboard');

    // Settings Management
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::get('/settings/site', [SettingsController::class, 'site'])->name('settings.site');
    Route::get('/settings/security', [SettingsController::class, 'security'])->name('settings.security');
    Route::get('/settings/users', [SettingsController::class, 'users'])->name('settings.users');

    // Posts Management
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', [\App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [\App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsController::class, 'edit'])->name('edit');
        Route::get('/{id}', [\App\Domains\ContentManagement\Posts\Http\Controllers\Admin\PostsController::class, 'show'])->name('show');
    });

    // Categories Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [\App\Domains\ContentManagement\Categories\Http\Controllers\Admin\CategoriesController::class, 'index'])->name('index');
        Route::get('/create', [\App\Domains\ContentManagement\Categories\Http\Controllers\Admin\CategoriesController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [\App\Domains\ContentManagement\Categories\Http\Controllers\Admin\CategoriesController::class, 'edit'])->name('edit');
    });

    // Tags Management
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::get('/', [\App\Domains\ContentManagement\Tags\Http\Controllers\Admin\TagsController::class, 'index'])->name('index');
        Route::get('/create', [\App\Domains\ContentManagement\Tags\Http\Controllers\Admin\TagsController::class, 'create'])->name('create');
        Route::get('/{id}/edit', [\App\Domains\ContentManagement\Tags\Http\Controllers\Admin\TagsController::class, 'edit'])->name('edit');
    });

    // Content Management
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('admin/content/Index');
        })->name('index');

        Route::get('/media', function () {
            return Inertia::render('admin/content/Media');
        })->name('media');

        Route::get('/comments', function () {
            return Inertia::render('admin/content/Comments');
        })->name('comments');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('admin/users/Index');
        })->name('index');

        Route::get('/{id}', function ($id) {
            return Inertia::render('admin/users/Show', ['id' => $id]);
        })->name('show');

        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('admin/users/Edit', ['id' => $id]);
        })->name('edit');
    });

    // Analytics & Reports
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('admin/analytics/Dashboard');
        })->name('dashboard');

        Route::get('/reports', function () {
            return Inertia::render('admin/analytics/Reports');
        })->name('reports');
    });

    // Pages Management
    Route::prefix('pages')->name('pages.')->group(function () {
        Route::get('/', [\App\Domains\ContentManagement\Pages\Controllers\PagesController::class, 'index'])->name('index');
        Route::get('/create', [\App\Domains\ContentManagement\Pages\Controllers\PagesController::class, 'create'])->name('create');
        Route::post('/', [\App\Domains\ContentManagement\Pages\Controllers\PagesController::class, 'store'])->name('store');
        Route::get('/{page}/edit', [\App\Domains\ContentManagement\Pages\Controllers\PagesController::class, 'edit'])->name('edit');
        Route::put('/{page}', [\App\Domains\ContentManagement\Pages\Controllers\PagesController::class, 'update'])->name('update');
        Route::delete('/{page}', [\App\Domains\ContentManagement\Pages\Controllers\PagesController::class, 'destroy'])->name('destroy');
    });
});
