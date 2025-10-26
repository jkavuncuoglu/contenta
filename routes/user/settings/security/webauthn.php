<?php

use App\Http\Controllers\WebAuthnController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // Get registration options
    Route::post('/webauthn/register/options', [WebAuthnController::class, 'registerOptions'])
        ->name('webauthn.register.options');

    // Register a new credential
    Route::post('/webauthn/register', [WebAuthnController::class, 'register'])
        ->name('webauthn.register');

    // List all credentials
    Route::get('/webauthn/credentials', [WebAuthnController::class, 'list'])
        ->name('webauthn.credentials.list');

    // Update credential name
    Route::patch('/webauthn/credentials/{id}', [WebAuthnController::class, 'update'])
        ->name('webauthn.credentials.update');

    // Delete credential
    Route::delete('/webauthn/credentials/{id}', [WebAuthnController::class, 'destroy'])
        ->name('webauthn.credentials.destroy');
});

