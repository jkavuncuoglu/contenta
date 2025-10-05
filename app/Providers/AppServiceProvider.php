<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Security\Contracts\TwoFactorAuthenticationServiceInterface;
use App\Domains\Security\Services\TwoFactorAuthenticationService;
use PragmaRX\Google2FA\Google2FA;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
