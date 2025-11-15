<?php

use App\Domains\Settings\SiteSettings\Http\Controllers\Settings\ProfileController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'profile',
    'as' => 'profile.',
], function () {
    Route::get('', [ProfileController::class, 'edit'])->name('edit');

    Route::patch('', [ProfileController::class, 'update'])->name('update');
    Route::delete('', [ProfileController::class, 'destroy'])->name('destroy');
});
