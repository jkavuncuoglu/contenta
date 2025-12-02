<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder;

use App\Domains\PageBuilder\Services\PageRenderService;
use App\Domains\PageBuilder\Services\PageRenderServiceContract;
use App\Domains\PageBuilder\Services\MarkdownRenderService;
use App\Domains\PageBuilder\Services\MarkdownRenderServiceContract;
use Illuminate\Support\ServiceProvider;

class PageBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PageRenderServiceContract::class, function ($app) {
            return new PageRenderService();
        });

        $this->app->singleton(MarkdownRenderServiceContract::class, function ($app) {
            return new MarkdownRenderService(
                $app->make(\App\Domains\ContentManagement\Services\ShortcodeParser\ShortcodeParserServiceContract::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load routes if routes file exists
        if (file_exists(__DIR__ . '/Http/routes.php')) {
            $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');
        }

        // Load migrations if migrations directory exists
        if (is_dir(__DIR__ . '/Database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        }
    }
}
