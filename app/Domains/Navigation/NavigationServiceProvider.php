<?php

declare(strict_types=1);

namespace App\Domains\Navigation;

use App\Domains\Navigation\Services\MenuService;
use App\Domains\Navigation\Services\MenuServiceContract;
use Illuminate\Support\ServiceProvider;

class NavigationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(MenuServiceContract::class, function ($app) {
            return new MenuService();
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
