<?php

namespace App\Domains\ContentManagement\Pages;

use App\Domains\PageBuilder\Services\PageBuilderServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * Alias for PageBuilder facade for ContentManagement compatibility
 */
class PageFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PageBuilderServiceContract::class;
    }
}
