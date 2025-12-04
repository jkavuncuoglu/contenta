<?php

namespace App\Domains\ContentManagement\Pages;

use App\Domains\ContentManagement\Pages\Services\PageServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * Page service facade for accessing page operations
 */
class PageFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PageServiceContract::class;
    }
}
