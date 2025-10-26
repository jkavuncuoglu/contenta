<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'user',
    'as' => 'user.',
    'middleware' => ['auth', 'verified']
], function () {
    Route::get('/', function () {
        return redirect()->route('user.settings.profile.edit');
    })->name('index');

    require __DIR__ . '/user/settings.php';
});
