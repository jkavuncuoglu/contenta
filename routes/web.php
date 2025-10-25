<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Domains\Settings\SiteSettings\Http\Controllers\Settings\ProfileController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/check-username', [RegisteredUserController::class, 'checkUsername']);


require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/two-factor.php';

require __DIR__.'/profile.php';
