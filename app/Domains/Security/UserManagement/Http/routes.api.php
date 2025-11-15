<?php

use App\Domains\Security\Authentication\Http\Controllers\AuthenticationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public routes
    Route::prefix('auth')->group(function () {
        // Explicit POST endpoints for authentication flows via consolidated UserController
        Route::post('register', [AuthenticationController::class, 'register']);
        Route::post('login', [AuthenticationController::class, 'login']);
        Route::post('forgot-password', [AuthenticationController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthenticationController::class, 'resetPassword']);

        // Handle CORS preflight for any auth path
        Route::options('{any}', fn () => response()->noContent())->where('any', '.*');
    });
});
