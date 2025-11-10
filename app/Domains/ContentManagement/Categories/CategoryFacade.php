<?php

namespace App\Domains\ContentManagement\Categories;

use Illuminate\Support\Facades\Facade;
use App\Domains\ContentManagement\Categories\Services\CategoryServiceContract;

class CategoryFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Domains\ContentManagement\Categories\Services\CategoryServiceContract::class;
    }
}
