<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Domains\ContentManagement\Tags\TagServiceProvider::class,
    App\Domains\ContentManagement\Categories\CategoryServiceProvider::class,
    App\Domains\ContentManagement\ContentManagementServiceProvider::class,
];
