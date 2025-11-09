<?php

use Illuminate\Support\Facades\Route;

// controller classes are imported via FQCN when used or via proper use statements if available

Route::prefix('v1')->group(function () {
    // Public routes
    Route::prefix('auth')->group(function () {
        // Explicit POST endpoints for authentication flows via consolidated UserController
        Route::post('register', [\App\Domains\Security\Authentication\Http\Controllers\AuthenticationController::class, 'register']);
        Route::post('login', [\App\Domains\Security\Authentication\Http\Controllers\AuthenticationController::class, 'login']);
        Route::post('forgot-password', [\App\Domains\Security\Authentication\Http\Controllers\AuthenticationController::class, 'forgotPassword']);
        Route::post('reset-password', [\App\Domains\Security\Authentication\Http\Controllers\AuthenticationController::class, 'resetPassword']);

        // Handle CORS preflight for any auth path
        Route::options('{any}', fn() => response()->noContent())->where('any', '.*');
    });
});
