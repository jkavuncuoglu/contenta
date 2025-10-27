<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'settings',
    'as' => 'settings.',
    'middleware' => ['auth'],
], function () {
    // Redirect top-level /settings to the profile by default
    Route::redirect('', 'settings/profile');

    require __DIR__ . '/settings/profile.php';
    require __DIR__ . '/settings/security.php';
    require __DIR__ . '/settings/api-tokens.php';
});
