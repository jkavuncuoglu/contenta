<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,

    // Content Management Domain
    App\Domains\ContentManagement\Tags\TagServiceProvider::class,
    App\Domains\ContentManagement\Categories\CategoryServiceProvider::class,
    App\Domains\ContentManagement\ContentManagementServiceProvider::class,

    // Media Domain
    App\Domains\Media\MediaServiceProvider::class,

    // Navigation Domain
    App\Domains\Navigation\NavigationServiceProvider::class,

    // PageBuilder Domain
    App\Domains\PageBuilder\PageBuilderServiceProvider::class,

    // Settings Domain
    App\Domains\Settings\SettingsServiceProvider::class,

    // Plugins Domain
    App\Domains\Plugins\PluginServiceProvider::class,
];
