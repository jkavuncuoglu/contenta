<?php

namespace App\Domains\ContentManagement\Services;

use App\Domains\ContentManagement\Categories\CategoryFacade;
use App\Domains\ContentManagement\Pages\PageFacade;
use App\Domains\ContentManagement\Posts\PostFacade;
use App\Domains\ContentManagement\Tags\TagFacade;

class ContentManagementService implements ContentManagementServiceContract
{
    public function posts()
    {
        return PostFacade::class;
    }

    public function pages()
    {
        return PageFacade::class;
    }

    public function tags()
    {
        return TagFacade::class;
    }

    public function categories()
    {
        return CategoryFacade::class;
    }
}
