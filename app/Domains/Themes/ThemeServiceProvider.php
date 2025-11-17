<?php

declare(strict_types=1);

namespace App\Domains\Themes;

use App\Domains\Themes\Services\ThemeService;
use App\Domains\Themes\Services\ThemeServiceContract;
use Illuminate\Support\ServiceProvider;

class ThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ThemeServiceContract::class, function ($app) {
            return new ThemeService;
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes if routes file exists
        if (file_exists(__DIR__.'/Http/routes.php')) {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
    }
}
