<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domains\Security\Contracts\TwoFactorAuthenticationServiceInterface;
use App\Domains\Security\Services\TwoFactorAuthenticationService;
use PragmaRX\Google2FA\Google2FA;
use App\Domains\ContentManagement\Pages\Services\PagesServiceContract;
use App\Domains\ContentManagement\Pages\Services\PagesService;
use App\Domains\Media\Services\MediaServiceContract;
use App\Domains\Media\Services\MediaService;
use App\Domains\ContentManagement\Comments\Services\CommentsServiceContract;
use App\Domains\ContentManagement\Comments\Services\CommentsService;

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

        $this->app->singleton(MediaServiceContract::class, function ($app) {
            return new MediaService();
        });

        $this->app->singleton(CommentsServiceContract::class, function ($app) {
            return new CommentsService();
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
