<?php

namespace App\Domains\Plugins;

use App\Domains\Plugins\Console\Commands\PluginDisableCommand;
use App\Domains\Plugins\Console\Commands\PluginDiscoverCommand;
use App\Domains\Plugins\Console\Commands\PluginEnableCommand;
use App\Domains\Plugins\Console\Commands\PluginInstallCommand;
use App\Domains\Plugins\Console\Commands\PluginScanCommand;
use App\Domains\Plugins\Features\PluginFeature;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register plugin feature flags
        Feature::define('plugin.*', function ($scope, string $slug) {
            return PluginFeature::resolve($scope, $slug);
        });

        // Load enabled plugins after app has booted
        $this->app->booted(function () {
            try {
                $pluginService = app(Services\PluginService::class);
                $pluginService->loadEnabledPlugins();
            } catch (\Exception $e) {
                // Silently fail during migrations or when tables don't exist yet
                $message = $e->getMessage();
                if (! str_contains($message, 'does not exist') && ! str_contains($message, 'no such table')) {
                    throw $e;
                }
            }
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                PluginScanCommand::class,
                PluginEnableCommand::class,
                PluginDisableCommand::class,
                PluginInstallCommand::class,
                PluginDiscoverCommand::class,
            ]);
        }
    }
}
