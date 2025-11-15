<?php

use App\Domains\ContentManagement\Posts\Jobs\PublishScheduledPosts;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Publish scheduled posts every minute
Schedule::job(new PublishScheduledPosts)
    ->everyMinute()
    ->name('publish-scheduled-posts')
    ->withoutOverlapping();
