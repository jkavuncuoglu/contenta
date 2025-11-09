<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Services;

use App\Domains\PageBuilder\Models\Block;
use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;

interface PageBuilderServiceContract
{
    /**
     * Get paginated pages
     */
    public function getPaginatedPages(int $perPage = 20): LengthAwarePaginator;

    /**
     * Create a new page
     */
    public function createPage(array $data): Page;

    /**
     * Update a page
     */
    public function updatePage(Page $page, array $data): Page;

    /**
     * Delete a page
     */
    public function deletePage(Page $page): bool;

    /**
     * Publish a page
     */
    public function publishPage(Page $page): Page;

    /**
     * Unpublish a page
     */
    public function unpublishPage(Page $page): Page;

    /**
     * Duplicate a page
     */
    public function duplicatePage(Page $page, string $newTitle): Page;

    /**
     * Get page by slug
     */
    public function getPageBySlug(string $slug): ?Page;

    /**
     * Get all layouts
     */
    public function getAllLayouts(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get all blocks
     */
    public function getAllBlocks(): \Illuminate\Database\Eloquent\Collection;

    /**
     * Get active blocks
     */
    public function getActiveBlocks(): \Illuminate\Database\Eloquent\Collection;
}
