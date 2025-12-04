<?php

use App\Domains\ContentManagement\Posts\Jobs\PublishScheduledPosts;
use App\Domains\SocialMedia\Jobs\ProcessDailyScheduledPosts;
use App\Domains\SocialMedia\Jobs\PublishScheduledSocialPosts;
use App\Domains\SocialMedia\Jobs\RefreshSocialAccountTokens;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Publish scheduled blog posts every minute
Schedule::job(new PublishScheduledPosts)
    ->everyMinute()
    ->name('publish-scheduled-posts')
    ->withoutOverlapping();

// Social Media Scheduler (Phase 5)
// Publish scheduled social media posts every minute
Schedule::job(new PublishScheduledSocialPosts)
    ->everyMinute()
    ->name('publish-scheduled-social-posts')
    ->withoutOverlapping();

// Process daily scheduled social posts at 9:00 AM
// Note: Individual accounts can have their own scheduled_post_time
Schedule::job(new ProcessDailyScheduledPosts)
    ->dailyAt('09:00')
    ->name('process-daily-scheduled-posts')
    ->withoutOverlapping();

// Refresh expiring social account tokens every hour
Schedule::job(new RefreshSocialAccountTokens)
    ->hourly()
    ->name('refresh-social-account-tokens')
    ->withoutOverlapping();
