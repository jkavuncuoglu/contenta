<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::group([
    'middleware' => ['auth', 'verified'],
    'prefix' => 'dashboard',
    'as' => 'dashboard.',
], function () {
    // Admin root redirect to dashboard
    Route::get('/', function () {
        return Inertia::render('admin/Dashboard');
    })->name('index');
});
