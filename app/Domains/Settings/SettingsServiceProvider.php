<?php

namespace App\Domains\Settings;

use Illuminate\Support\ServiceProvider;
use App\Domains\Settings\Contracts\SettingsContract;
use App\Domains\Settings\Service$1;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(SettingsContract::class, SettingsService::class);

        // Register the Settings facade
        $this->app->bind('settings', function($app) {
            return $app->make(SettingsContract::class);
        });
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
        $this->loadMigrationsFrom(__DIR__.'/../../../database/migrations');
    }
}
