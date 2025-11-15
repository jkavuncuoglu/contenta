<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder;

use App\Domains\PageBuilder\Services\PageBuilderServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Pagination\LengthAwarePaginator getPaginatedPages(int $perPage = 20)
 * @method static \App\Domains\PageBuilder\Models\Page createPage(array<string, mixed> $data)
 * @method static \App\Domains\PageBuilder\Models\Page updatePage(\App\Domains\PageBuilder\Models\Page $page, array<string, mixed> $data)
 * @method static bool deletePage(\App\Domains\PageBuilder\Models\Page $page)
 * @method static \App\Domains\PageBuilder\Models\Page publishPage(\App\Domains\PageBuilder\Models\Page $page)
 * @method static \App\Domains\PageBuilder\Models\Page unpublishPage(\App\Domains\PageBuilder\Models\Page $page)
 * @method static \App\Domains\PageBuilder\Models\Page duplicatePage(\App\Domains\PageBuilder\Models\Page $page, string $newTitle)
 * @method static \App\Domains\PageBuilder\Models\Page|null getPageBySlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Collection getAllLayouts()
 * @method static \Illuminate\Database\Eloquent\Collection getAllBlocks()
 * @method static \Illuminate\Database\Eloquent\Collection getActiveBlocks()
 *
 * @see \App\Domains\PageBuilder\Services\PageBuilderService
 */
class PageBuilderFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return PageBuilderServiceContract::class;
    }
}
