<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Pages\Services;

use App\Domains\ContentManagement\ContentStorage\Models\ContentData;
use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Services\MarkdownRenderServiceContract;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PageService implements PageServiceContract
{
    public function __construct(
        private readonly MarkdownRenderServiceContract $markdownRenderer
    ) {}

    /**
     * Get paginated pages
     *
     * @return LengthAwarePaginator<int, Page>
     */
    public function getPaginatedPages(int $perPage = 20, ?string $status = null): LengthAwarePaginator
    {
        $query = Page::query()
            ->with(['author', 'parent']);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Create a new page
     *
     * @param  array<string, mixed>  $data
     */
    public function createPage(array $data): Page
    {
        // Generate slug if not provided
        if (empty($data['slug']) && isset($data['title']) && is_string($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Set default status
        if (empty($data['status'])) {
            $data['status'] = 'draft';
        }

        // Set default storage driver
        if (empty($data['storage_driver'])) {
            $data['storage_driver'] = 'database';
        }

        // Extract content from data
        $content = null;
        if (isset($data['content'])) {
            $markdown = is_string($data['content']) ? $data['content'] : '';
            $content = ContentData::parse($markdown);
            unset($data['content']);
        }

        // Create page
        $page = Page::create($data);

        // Save content to storage if provided
        if ($content) {
            $page->content = $content;
            $page->save();
        }

        return $page;
    }

    /**
     * Update a page
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePage(Page $page, array $data): Page
    {
        // Update slug if title changed
        if (isset($data['title']) && is_string($data['title']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        // Handle content update
        if (isset($data['content'])) {
            $markdown = is_string($data['content']) ? $data['content'] : '';
            $content = ContentData::parse($markdown);
            $page->content = $content;
            unset($data['content']);
        }

        // Update page attributes
        $page->update($data);

        $freshPage = $page->fresh();
        assert($freshPage instanceof Page);

        return $freshPage;
    }

    /**
     * Delete a page
     */
    public function deletePage(Page $page): bool
    {
        return (bool) $page->delete();
    }

    /**
     * Publish a page
     */
    public function publishPage(Page $page): Page
    {
        $page->publish();

        $freshPage = $page->fresh();
        assert($freshPage instanceof Page);

        return $freshPage;
    }

    /**
     * Unpublish a page
     */
    public function unpublishPage(Page $page): Page
    {
        $page->unpublish();

        $freshPage = $page->fresh();
        assert($freshPage instanceof Page);

        return $freshPage;
    }

    /**
     * Archive a page
     */
    public function archivePage(Page $page): Page
    {
        $page->archive();

        $freshPage = $page->fresh();
        assert($freshPage instanceof Page);

        return $freshPage;
    }

    /**
     * Render page content as HTML
     */
    public function renderPage(Page $page): string
    {
        $content = $page->content;

        if (! $content) {
            return '';
        }

        return $this->markdownRenderer->render($content->getContent());
    }

    /**
     * Duplicate a page
     */
    public function duplicatePage(Page $page): Page
    {
        $newPage = $page->replicate();
        $newPage->title = $page->title.' (Copy)';
        $newPage->slug = Str::slug($newPage->title).'-'.Str::random(6);
        $newPage->status = Page::STATUS_DRAFT;
        $newPage->published_at = null;
        $newPage->save();

        // Copy content to new storage path
        if ($page->content) {
            $newPage->content = $page->content;
            $newPage->save();
        }

        return $newPage;
    }
}
