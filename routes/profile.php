<?php
use Illuminate\Support\Facades\Route;
use App\Domains\Settings\SiteSettings\Http\Controllers\Settings\ProfileController;

Route::group([
    'prefix' => 'profile',
    'as' => 'profile.',
], function () {
    Route::get('', [ProfileController::class, 'edit'])->name('edit');

    Route::patch('', [ProfileController::class, 'update'])->name('update');
});
