<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder;

use App\Domains\PageBuilder\Services\PageBuilderService;
use App\Domains\PageBuilder\Services\PageBuilderServiceContract;
use App\Domains\PageBuilder\Services\PageRenderService;
use App\Domains\PageBuilder\Services\PageRenderServiceContract;
use Illuminate\Support\ServiceProvider;

class PageBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PageBuilderServiceContract::class, function ($app) {
            return new PageBuilderService;
        });

        $this->app->singleton(PageRenderServiceContract::class, function ($app) {
            return new PageRenderService;
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
