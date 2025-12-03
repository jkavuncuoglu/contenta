<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Pages\Services;

use App\Domains\ContentManagement\Pages\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;

interface PageServiceContract
{
    /**
     * Get paginated pages
     *
     * @return LengthAwarePaginator<int, Page>
     */
    public function getPaginatedPages(int $perPage = 20, ?string $status = null): LengthAwarePaginator;

    /**
     * Create a new page
     *
     * @param array<string, mixed> $data
     */
    public function createPage(array $data): Page;

    /**
     * Update a page
     *
     * @param array<string, mixed> $data
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
     * Archive a page
     */
    public function archivePage(Page $page): Page;

    /**
     * Render page content as HTML
     */
    public function renderPage(Page $page): string;

    /**
     * Duplicate a page
     */
    public function duplicatePage(Page $page): Page;
}
