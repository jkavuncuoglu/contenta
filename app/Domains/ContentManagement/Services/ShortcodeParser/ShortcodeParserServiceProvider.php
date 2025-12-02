<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser;

use Illuminate\Support\ServiceProvider;

class ShortcodeParserServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->app->singleton(ShortcodeParserServiceContract::class, function ($app) {
            return new ShortcodeParserService;
        });
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        //
    }
}
