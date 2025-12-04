<?php

declare(strict_types=1);

use App\Domains\SocialMedia\Http\Controllers\Admin\OAuthController;
use App\Domains\SocialMedia\Http\Controllers\Admin\SocialAccountController;
// use App\Domains\SocialMedia\Http\Controllers\Admin\SocialAnalyticsController; // TODO: Create this controller
use App\Domains\SocialMedia\Http\Controllers\Admin\SocialPostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Social Media Routes
|--------------------------------------------------------------------------
|
| Routes for social media account management, OAuth, posts, and analytics.
|
*/

Route::middleware(['web', 'auth'])->prefix('admin/social-media')->name('admin.social-media.')->group(function () {
    // OAuth Routes
    Route::prefix('oauth')->name('oauth.')->group(function () {
        Route::get('/{platform}/authorize', [OAuthController::class, 'authorize'])->name('authorize');
        Route::get('/{platform}/callback', [OAuthController::class, 'callback'])->name('callback');
        Route::post('/{accountId}/refresh', [OAuthController::class, 'refresh'])->name('refresh');
        Route::post('/{accountId}/disconnect', [OAuthController::class, 'disconnect'])->name('disconnect');
    });

    // Social Accounts Management
    Route::prefix('accounts')->name('accounts.')->group(function () {
        Route::get('/', [SocialAccountController::class, 'index'])->name('index');
        Route::get('/{account}', [SocialAccountController::class, 'show'])->name('show');
        Route::get('/{account}/edit', [SocialAccountController::class, 'edit'])->name('edit');
        Route::put('/{account}', [SocialAccountController::class, 'update'])->name('update');
        Route::delete('/{account}', [SocialAccountController::class, 'destroy'])->name('destroy');
        Route::post('/{account}/verify', [SocialAccountController::class, 'verify'])->name('verify');
    });

    // Social Posts Management
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::get('/', [SocialPostController::class, 'index'])->name('index');
        Route::get('/create', [SocialPostController::class, 'create'])->name('create');
        Route::post('/', [SocialPostController::class, 'store'])->name('store');
        Route::get('/{post}', [SocialPostController::class, 'show'])->name('show');
        Route::get('/{post}/edit', [SocialPostController::class, 'edit'])->name('edit');
        Route::put('/{post}', [SocialPostController::class, 'update'])->name('update');
        Route::delete('/{post}', [SocialPostController::class, 'destroy'])->name('destroy');
        Route::post('/{post}/publish', [SocialPostController::class, 'publish'])->name('publish');
        Route::post('/{post}/cancel', [SocialPostController::class, 'cancel'])->name('cancel');
        Route::post('/{post}/retry', [SocialPostController::class, 'retry'])->name('retry');
    });

    // Analytics (TODO: Implement SocialAnalyticsController)
    // Route::prefix('analytics')->name('analytics.')->group(function () {
    //     Route::get('/', [SocialAnalyticsController::class, 'index'])->name('index');
    //     Route::get('/account/{account}', [SocialAnalyticsController::class, 'account'])->name('account');
    //     Route::post('/sync/{post}', [SocialAnalyticsController::class, 'sync'])->name('sync');
    // });

    // API Helpers (for frontend)
    Route::prefix('api')->name('api.')->group(function () {
        Route::post('/posts/check-conflicts', [SocialPostController::class, 'checkConflicts'])->name('posts.check-conflicts');
        Route::get('/posts/scheduled', [SocialPostController::class, 'scheduled'])->name('posts.scheduled');
    });
});
