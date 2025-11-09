<?php

namespace App\Domains\Security\Authentication;

use App\Domains\Security\Authentication\Services\AuthenticationService;
use App\Domains\Security\Authentication\Services\AuthenticationServiceContract;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthenticationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthenticationServiceContract::class, AuthenticationService::class);
    }

    public function boot(): void
    {
        // Load web routes with the 'web' middleware stack
        Route::middleware('web')->group(__DIR__ . '/Http/routes.web.php');

        // Load API routes with '/api' prefix and 'api' middleware stack
        Route::prefix('api')
            ->middleware('api')
            ->group(__DIR__ . '/Http/routes.api.php');
    }
}
