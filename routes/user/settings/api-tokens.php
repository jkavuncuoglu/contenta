<?php

use App\Domains\Security\ApiTokens\Http\Controllers\ApiTokenController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'api-tokens',
    'as' => 'api-tokens.',
    'middleware' => ['auth', 'permission:api-tokens.use'],
], function () {
    Route::get('/', [ApiTokenController::class, 'index'])->name('index');
    Route::post('/', [ApiTokenController::class, 'store'])->name('store');
    // Delete all tokens via DELETE /user/settings/api-tokens
    Route::delete('/', [ApiTokenController::class, 'destroyAll'])->name('destroy-all');
    Route::delete('/{tokenId}', [ApiTokenController::class, 'destroy'])->name('destroy');
});
