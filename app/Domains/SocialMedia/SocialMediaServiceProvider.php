<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Observers\PostObserver;
use App\Domains\SocialMedia\Policies\SocialAccountPolicy;
use App\Domains\SocialMedia\Services\OAuthService;
use App\Domains\SocialMedia\Services\OAuthServiceContract;
use App\Domains\SocialMedia\Services\SchedulerService;
use App\Domains\SocialMedia\Services\SchedulerServiceContract;
use App\Domains\SocialMedia\Services\SocialMediaService;
use App\Domains\SocialMedia\Services\SocialMediaServiceContract;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SocialMediaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // OAuth Service
        $this->app->singleton(OAuthServiceContract::class, OAuthService::class);

        // Social Media Service
        $this->app->singleton(SocialMediaServiceContract::class, SocialMediaService::class);

        // Scheduler Service (Phase 5)
        $this->app->singleton(SchedulerServiceContract::class, SchedulerService::class);

        // Analytics Service (Phase 8)
        // $this->app->singleton(AnalyticsServiceContract::class, AnalyticsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');

        // Load routes
        if (file_exists(__DIR__.'/Http/routes.php')) {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        }

        // Register policies
        Gate::policy(SocialAccount::class, SocialAccountPolicy::class);

        // Register observers (Phase 5)
        Post::observe(PostObserver::class);
    }
}
