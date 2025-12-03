<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Pages;

use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Pages\Observers\PageObserver;
use App\Domains\ContentManagement\Pages\Services\PageService;
use App\Domains\ContentManagement\Pages\Services\PageServiceContract;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Bind PageService contract
        $this->app->bind(PageServiceContract::class, PageService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register Page model observer
        Page::observe(PageObserver::class);
    }
}
