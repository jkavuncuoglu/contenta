<?php

declare(strict_types=1);

use App\Domains\Calendar\Http\Controllers\Admin\CalendarController;
use Illuminate\Support\Facades\Route;

// Unified Calendar Routes
Route::middleware(['web', 'auth', 'permission:view calendar'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/api/calendar/data', [CalendarController::class, 'data'])->name('api.calendar.data');
});
