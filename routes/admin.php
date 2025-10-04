<?php

use App\Domains\Settings\Http\Controllers\Admin\SettingsController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', function () {
        return Inertia::render('admin/Dashboard');
    })->name('dashboard');

    // Settings Management
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('/settings', [SettingsController::class, 'update'])->name('settings.update');

    // Posts Management
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('admin/posts/Index');
        })->name('index');

        Route::get('/create', function () {
            return Inertia::render('admin/posts/Create');
        })->name('create');

        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('admin/posts/Edit', ['id' => $id]);
        })->name('edit');

        Route::get('/{id}', function ($id) {
            return Inertia::render('admin/posts/Show', ['id' => $id]);
        })->name('show');
    });

    // Categories Management
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('admin/categories/Index');
        })->name('index');

        Route::get('/create', function () {
            return Inertia::render('admin/categories/Create');
        })->name('create');

        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('admin/categories/Edit', ['id' => $id]);
        })->name('edit');
    });

    // Tags Management
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::get('/', function () {
            return Inertia::render('admin/tags/Index');
        })->name('index');

        Route::get('/create', function () {
            return Inertia::render('admin/tags/Create');
        })->name('create');

        Route::get('/{id}/edit', function ($id) {
            return Inertia::render('admin/tags/Edit', ['id' => $id]);
        })->name('edit');
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
});
