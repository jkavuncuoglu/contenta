<?php

use App\Domains\Settings\SiteSettings\Http\Controllers\Settings\PasswordController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'password',
    'as' => 'password.',
], function () {
    Route::get('', [PasswordController::class, 'edit'])->name('edit');
    Route::put('', [PasswordController::class, 'update'])->name('update');
});
