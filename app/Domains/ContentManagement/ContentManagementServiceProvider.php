<?php

namespace App\Domains\ContentManagement;

use Illuminate\Support\ServiceProvider;
use App\Domains\ContentManagement\Services\ContentManagementService;
use App\Domains\ContentManagement\Services\ContentManagementServiceContract;

class ContentManagementServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(ContentManagementServiceContract::class, ContentManagementService::class);
    }
}

