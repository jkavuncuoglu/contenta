<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement;

use App\Domains\ContentManagement\Posts\Services\PostService;
use App\Domains\ContentManagement\Posts\Services\PostServiceContract;
use App\Domains\ContentManagement\Services\ContentManagementService;
use App\Domains\ContentManagement\Services\ContentManagementServiceContract;
use Illuminate\Support\ServiceProvider;

class ContentManagementServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ContentManagementServiceContract::class, ContentManagementService::class);
        $this->app->singleton(PostServiceContract::class, PostService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load migrations if migrations directory exists
        if (is_dir(__DIR__ . '/Database/migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/Database/migrations');
        }
    }
}

