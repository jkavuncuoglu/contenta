<?php

namespace App\Domains\ContentManagement;

use Illuminate\Support\Facades\Facade;
use App\Domains\ContentManagement\Services\ContentManagementServiceContract;

class ContentManagementFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ContentManagementServiceContract::class;
    }

    public static function posts()
    {
        return app(ContentManagementServiceContract::class)->posts();
    }

    public static function pages()
    {
        return app(ContentManagementServiceContract::class)->pages();
    }

    public static function tags()
    {
        return app(ContentManagementServiceContract::class)->tags();
    }

    public static function categories()
    {
        return app(ContentManagementServiceContract::class)->categories();
    }
}

