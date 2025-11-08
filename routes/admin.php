<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::group([
    'middleware' => ['auth', 'verified'],
    'prefix' => 'admin',
    'as' => 'admin.',
], function () {
    // Admin root redirect to dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard.index');
    });

    require __DIR__.'/admin/dashboard.php';

    require __DIR__ . '/admin/content.php';

    require __DIR__.'/admin/settings.php';

    require __DIR__.'/admin/pagebuilder.php';
});
