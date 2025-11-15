<?php

namespace App\Domains\ContentManagement\Categories;

use Illuminate\Support\Facades\Facade;

class CategoryFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Domains\ContentManagement\Categories\Services\CategoryServiceContract::class;
    }
}
