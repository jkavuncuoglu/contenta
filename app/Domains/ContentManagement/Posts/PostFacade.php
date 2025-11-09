<?php

namespace App\Domains\ContentManagement\Posts;

use App\Domains\ContentManagement\Posts\Services\PostServiceContract;
use Illuminate\Support\Facades\Facade;

class PostFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PostServiceContract::class;
    }
}
