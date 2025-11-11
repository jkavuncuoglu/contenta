<?php

namespace App\Domains\Plugins;

use App\Domains\Plugins\Console\Commands\PluginDiscoverCommand;
use App\Domains\Plugins\Console\Commands\PluginDisableCommand;
use App\Domains\Plugins\Console\Commands\PluginEnableCommand;
use App\Domains\Plugins\Console\Commands\PluginInstallCommand;
use App\Domains\Plugins\Console\Commands\PluginScanCommand;
use App\Domains\Plugins\Features\PluginFeature;
use Illuminate\Support\ServiceProvider;
use Laravel\Pennant\Feature;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        // Load enabled plugins after app has booted (skip if DB unreachable or table missing)
        $this->app->booted(function () {
            if ($this->shouldSkipPluginLoading()) {
                return; // CI/composer install/missing DB
            }

            if (! $this->databaseReady()) {
                return; // Cannot reach DB or plugins table absent yet
            }

            try {
                $pluginService = app(Services\PluginService::class);
                $pluginService->loadEnabledPlugins();
            } catch (\Throwable $e) {
                $message = $e->getMessage();
                // Swallow common early boot DB errors
                if (
                    str_contains($message, 'does not exist') ||
                    str_contains($message, 'no such table') ||
                    str_contains($message, 'Connection refused') ||
                    str_contains($message, 'SQLSTATE[HY000]') ||
                    str_contains($message, 'SQLSTATE[2002]')
                ) {
                    return;
                }
                throw $e; // Unexpected; bubble up
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

    /** Determine if plugin loading should be skipped (e.g. during composer install or explicit env flag) */
    protected function shouldSkipPluginLoading(): bool
    {
        // Allow explicit opt-out via env
        if (config('app.skip_plugin_loading', false)) {
            return true;
        }
        // During certain artisan commands (migrate, migrate:*, db:seed, package:discover) skip to avoid connection errors
        if ($this->app->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            foreach ($argv as $arg) {
                if (preg_match('/^(migrate|db:seed|package:discover|config:cache|config:clear)$/', $arg)) {
                    return true;
                }
            }
        }
        return false;
    }

    /** Check if we can reach the DB and the plugins table exists */
    protected function databaseReady(): bool
    {
        try {
            // Try a lightweight PDO connection
            DB::connection()->getPdo();
            // Check table existence without triggering exception
            return Schema::hasTable('plugins');
        } catch (\Throwable $e) {
            return false;
        }
    }
}
