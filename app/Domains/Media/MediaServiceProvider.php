<?php

declare(strict_types=1);

namespace App\Domains\Media;

use App\Domains\Media\Services\MediaService;
use App\Domains\Media\Services\MediaServiceContract;
use Illuminate\Support\ServiceProvider;

class MediaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(MediaServiceContract::class, function ($app) {
            return new MediaService;
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

        // Load migrations if migrations directory exists
        if (is_dir(__DIR__.'/Database/migrations')) {
            $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
        }
    }
}
