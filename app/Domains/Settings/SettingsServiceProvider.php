<?php

namespace App\Domains\Settings;

use App\Domains\Settings\Services\SiteSettingsService;
use App\Domains\Settings\Services\SiteSettingsServiceContract;
use App\Domains\Settings\Services\ThemeSettingsService;
use App\Domains\Settings\Services\ThemeSettingsServiceContract;
use Illuminate\Support\ServiceProvider;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(SiteSettingsServiceContract::class, SiteSettingsService::class);
        $this->app->singleton(ThemeSettingsServiceContract::class, ThemeSettingsService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__.'/../../../config/settings.php' => config_path('settings.php'),
        ], 'config');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
    }
}
