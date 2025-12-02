<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Media\Services\MediaServiceContract;
use App\Domains\Media\Services\MediaService;
use App\Domains\ContentManagement\Comments\Services\CommentsServiceContract;
use App\Domains\ContentManagement\Comments\Services\CommentsService;
use App\Domains\Settings\Services\SiteSettingsServiceContract;
use App\Domains\Settings\Services\SiteSettingsService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // TwoFactorAuthentication is handled by Laravel Fortify

        $this->app->singleton(MediaServiceContract::class, function ($app) {
            return new MediaService();
        });

        $this->app->singleton(CommentsServiceContract::class, function ($app) {
            return new CommentsService();
        });

        $this->app->singleton(SiteSettingsServiceContract::class, function ($app) {
            return new SiteSettingsService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register console commands after all service bindings are in place
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\MigrateToMarkdownCommand::class,
            ]);
        }
    }
}
