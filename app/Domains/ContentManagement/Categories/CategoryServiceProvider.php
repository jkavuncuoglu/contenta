<?php

namespace App\Domains\ContentManagement\Categories;

use Illuminate\Support\ServiceProvider;
use App\Domains\ContentManagement\Categories\Services\CategoryService;
use App\Domains\ContentManagement\Categories\Services\CategoryServiceContract;

class CategoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CategoryServiceContract::class, CategoryService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
    }
}

