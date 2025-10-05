<?php
namespace App\Domains\ContentManagement\Tags;

use Illuminate\Support\ServiceProvider;
use App\Domains\ContentManagement\Tags\Services\TagServiceContract;
use App\Domains\ContentManagement\Tags\Services\TagService;

class TagServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TagServiceContract::class, function ($app) {
            return new TagService();
        });
    }

    public function boot(): void
    {
        // Load Tag migrations from domain
        $this->loadMigrationsFrom(
            base_path('app/Domains/ContentManagement/Tags/Database/migrations')
        );
    }
}
