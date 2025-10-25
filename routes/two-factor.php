<?php

use Illuminate\Support\Facades\Route;
use App\Domains\Settings\SiteSettings\Http\Controllers\Settings\TwoFactorAuthenticationController;

Route::middleware('auth')->group(function () {
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('two-factor', [TwoFactorAuthenticationController::class, 'show'])->name('two-factor.show');
    });

    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('setup', [TwoFactorAuthenticationController::class, 'getSetupData'])->name('setup');
        Route::post('enable', [TwoFactorAuthenticationController::class, 'enable'])->name('enable');
        Route::delete('disable', [TwoFactorAuthenticationController::class, 'disable'])->name('disable');

        Route::get('recovery-codes', [TwoFactorAuthenticationController::class, 'getRecoveryCodes'])->name('recovery-codes');
        Route::get('recovery-codes/download', [TwoFactorAuthenticationController::class, 'downloadRecoveryCodes'])->name('recovery-codes.download');
        Route::post('recovery-codes/regenerate', [TwoFactorAuthenticationController::class, 'requestRegeneration'])->name('recovery-codes.regenerate');
        Route::get('recovery-codes/confirm', [TwoFactorAuthenticationController::class, 'confirmRegeneration'])->name('recovery-codes.confirm');
    });
});
