<?php

namespace App\Domains\ContentManagement\Tags;

use App\Domains\ContentManagement\Tags\Services\TagServiceContract;
use Illuminate\Support\Facades\Facade;

class TagFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TagServiceContract::class;
    }
}
