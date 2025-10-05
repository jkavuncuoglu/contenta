<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Security\Contracts\TwoFactorAuthenticationServiceInterface;
use App\Domains\Security\Services\TwoFactorAuthenticationService;
use PragmaRX\Google2FA\Google2FA;
use App\Domains\ContentManagement\Pages\Services\PagesServiceContract;
use App\Domains\ContentManagement\Pages\Services\PagesService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TwoFactorAuthenticationServiceInterface::class, function ($app) {
            return new TwoFactorAuthenticationService(new Google2FA());
        });

        $this->app->singleton(PagesServiceContract::class, function ($app) {
            return new PagesService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
