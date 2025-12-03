<?php

use App\Domains\Security\Http\Controllers\Admin\RolesController;
use App\Domains\Security\UserManagement\Http\Controllers\UserManagementController;
use App\Domains\Settings\SiteSettings\Http\Controllers\Settings\PasswordController;
use App\Domains\Settings\SiteSettings\Http\Controllers\Settings\ProfileController;
use App\Domains\Settings\SiteSettings\Http\Controllers\Settings\TwoFactorAuthenticationController;
use App\Domains\Settings\Http\Controllers\Admin\SiteSettingsController;
use App\Domains\Settings\Http\Controllers\Admin\SecuritySettingsController;
use App\Domains\Settings\Http\Controllers\Admin\ThemeSettingsController;
use App\Domains\ContentManagement\ContentStorage\Http\Controllers\Admin\ContentStorageSettingsController;
use App\Domains\ContentManagement\ContentStorage\Http\Controllers\Admin\ContentMigrationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group([
    'prefix' => 'settings',
    'as' => 'settings.',
    'middleware' => ['auth']
], function () {
    Route::redirect('', 'settings/site');

    Route::group([
        'prefix' => 'users',
        'as' => 'users.',
    ], function () {
        Route::get('', [UserManagementController::class, 'index'])->name('index');

        Route::group([
            'prefix' => '{id}',
            'as' => 'user.',
        ], function () {
            Route::group([
                'prefix' => 'profile',
                'as' => 'profile.',
            ], function () {
                Route::get('', function ($id) {
                    return Inertia::render('admin/users/Show', ['id' => $id]);
                })->name('show');

                Route::get('edit', function ($id) {
                    return Inertia::render('admin/users/Edit', ['id' => $id]);
                })->name('edit');

                Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
                Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
                Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
            });


            Route::group([
                'prefix' => 'password',
                'as' => 'password.',
            ], function () {
                Route::get('', [PasswordController::class, 'edit'])->name('password.edit');
                Route::put('', [PasswordController::class, 'update'])
                    ->middleware('throttle:6,1')
                    ->name('password.update');
            });

            Route::group([
                'prefix' => 'profile',
                'as' => 'profile.',
            ], function () {
                Route::get('', function ($id) {
                    return Inertia::render('admin/users/Show', ['id' => $id]);
                })->name('show');

                Route::get('edit', function ($id) {
                    return Inertia::render('admin/users/Edit', ['id' => $id]);
                })->name('edit');

                Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
                Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
                Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
            });

            Route::group([
                'prefix' => 'two-factor',
                'as' => 'two-factor.',
            ], function () {
                Route::get('', [TwoFactorAuthenticationController::class, 'show'])
                    ->name('show');
                Route::post('', [TwoFactorAuthenticationController::class, 'store'])
                    ->name('store');
                Route::delete('', [TwoFactorAuthenticationController::class, 'destroy'])
                    ->name('destroy');
            });
        });
    });

    Route::group([
        'prefix' => 'site',
        'as' => 'site.',
    ], function () {
        Route::get('', [SiteSettingsController::class, 'index'])->name('index');
        Route::put('', [SiteSettingsController::class, 'update'])->name('update');
    });

    Route::group([
        'prefix' => 'security',
        'as' => 'security.',
    ], function () {
        Route::get('', [SecuritySettingsController::class, 'index'])->name('index');
        Route::put('', [SecuritySettingsController::class, 'update'])->name('update');
    });

    Route::group([
        'prefix' => 'permissions',
        'as' => 'permissions.',
    ], function () {
        Route::get('', [RolesController::class, 'index'])->name('index');
        Route::post('roles', [RolesController::class, 'store'])->name('roles.store');
        Route::put('roles/{role}', [RolesController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}', [RolesController::class, 'destroy'])->name('roles.destroy');
    });

    Route::group([
        'prefix' => 'theme',
        'as' => 'theme.',
    ], function () {
        Route::get('', [ThemeSettingsController::class, 'index'])->name('index');
        Route::get('colors', [ThemeSettingsController::class, 'show'])->name('show');
        Route::put('', [ThemeSettingsController::class, 'update'])->name('update');
    });

    Route::group([
        'prefix' => 'content-storage',
        'as' => 'content-storage.',
    ], function () {
        Route::get('', [ContentStorageSettingsController::class, 'index'])->name('index');
        Route::put('', [ContentStorageSettingsController::class, 'update'])->name('update');
        Route::post('test-connection', [ContentStorageSettingsController::class, 'testConnection'])->name('test-connection');

        // Migration routes
        Route::get('migrate', [ContentMigrationController::class, 'index'])->name('migrate.index');
        Route::post('migrations', [ContentMigrationController::class, 'store'])->name('migrations.store');
        Route::get('migrations', [ContentMigrationController::class, 'list'])->name('migrations.list');
        Route::get('migrations/{id}', [ContentMigrationController::class, 'show'])->name('migrations.show');
        Route::post('migrations/{id}/verify', [ContentMigrationController::class, 'verify'])->name('migrations.verify');
        Route::post('migrations/{id}/rollback', [ContentMigrationController::class, 'rollback'])->name('migrations.rollback');
    });
});
