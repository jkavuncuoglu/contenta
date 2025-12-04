<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,

    // Content Management Domain
    App\Domains\ContentManagement\Tags\TagServiceProvider::class,
    App\Domains\ContentManagement\Categories\CategoryServiceProvider::class,
    App\Domains\ContentManagement\ContentManagementServiceProvider::class,
    App\Domains\ContentManagement\Services\ShortcodeParser\ShortcodeParserServiceProvider::class,

    // Media Domain
    App\Domains\Media\MediaServiceProvider::class,

    // Navigation Domain
    App\Domains\Navigation\NavigationServiceProvider::class,

    // Settings Domain
    App\Domains\Settings\SettingsServiceProvider::class,

    // Content Storage Subdomain (under ContentManagement)
    App\Domains\ContentManagement\ContentStorage\ContentStorageServiceProvider::class,

    // Pages Subdomain (under ContentManagement)
    App\Domains\ContentManagement\Pages\PageServiceProvider::class,

    // Themes Domain
    App\Domains\Themes\ThemeServiceProvider::class,

    // Plugins Domain
    App\Domains\Plugins\PluginServiceProvider::class,
];
