<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage;

use App\Domains\ContentManagement\ContentStorage\Services\ContentStorageManager;
use Illuminate\Support\ServiceProvider;

/**
 * Content Storage Service Provider
 *
 * Registers content storage services and repositories with the application container.
 * Follows Laravel's service provider pattern for domain-driven design.
 */
class ContentStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Register ContentStorageManager as singleton
        $this->app->singleton(ContentStorageManager::class, function ($app) {
            return new ContentStorageManager($app);
        });

        // Register facades/aliases if needed
        $this->app->alias(ContentStorageManager::class, 'content.storage');
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Load migrations from this domain
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Register console commands when running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\MigrateContentCommand::class,
                // Console\Commands\RollbackMigrationCommand::class,  // Phase 3 - Future
            ]);
        }

        // Register event listeners (future)
        // Event::listen(
        //     StorageDriverChanged::class,
        //     AutoMigrateListener::class
        // );
    }

    /**
     * Get the services provided by the provider
     *
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            ContentStorageManager::class,
            'content.storage',
        ];
    }
}
