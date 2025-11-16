<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Services;

use App\Domains\PageBuilder\Models\Block;
use App\Domains\PageBuilder\Models\Layout;
use App\Domains\PageBuilder\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class PageBuilderService implements PageBuilderServiceContract
{
    /**
     * Get paginated pages
     *
     * @return LengthAwarePaginator<Page>
     */
    public function getPaginatedPages(int $perPage = 20): LengthAwarePaginator
    {
        return Page::query()
            ->with(['layout'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new page
     *
     * @param  array<string, mixed>  $data
     */
    public function createPage(array $data): Page
    {
        if (empty($data['slug'])) {
            $title = $data['title'] ?? '';
            $baseSlug = Str::slug(is_string($title) ? $title : '');
            $data['slug'] = $this->generateUniqueSlug($baseSlug);
        }

        return Page::create($data);
    }

    /**
     * Update a page
     *
     * @param  array<string, mixed>  $data
     */
    public function updatePage(Page $page, array $data): Page
    {
        if (isset($data['title']) && empty($data['slug'])) {
            $title = $data['title'];
            $data['slug'] = Str::slug(is_string($title) ? $title : '');
        }

        $page->update($data);
        $freshPage = $page->fresh();
        if (! $freshPage) {
            throw new \Exception('Failed to refresh page after update');
        }

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
        $page->update([
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $freshPage = $page->fresh();
        if (! $freshPage) {
            throw new \Exception('Failed to refresh page after publishing');
        }

        return $freshPage;
    }

    /**
     * Unpublish a page
     */
    public function unpublishPage(Page $page): Page
    {
        $page->update([
            'status' => Page::STATUS_DRAFT,
            'published_at' => null,
        ]);

        $freshPage = $page->fresh();
        if (! $freshPage) {
            throw new \Exception('Failed to refresh page after unpublishing');
        }

        return $freshPage;
    }

    /**
     * Duplicate a page
     */
    public function duplicatePage(Page $page, string $newTitle): Page
    {
        $newPage = $page->replicate();
        $newPage->title = $newTitle;
        $newPage->slug = Str::slug($newTitle);
        $newPage->status = Page::STATUS_DRAFT;
        $newPage->published_at = null;
        $newPage->save();

        return $newPage;
    }

    /**
     * Get page by slug
     */
    public function getPageBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)
            ->where('status', Page::STATUS_PUBLISHED)
            ->first();
    }

    /**
     * Get all layouts
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Layout>
     */
    public function getAllLayouts(): \Illuminate\Database\Eloquent\Collection
    {
        return Layout::all();
    }

    /**
     * Get all blocks
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Block>
     */
    public function getAllBlocks(): \Illuminate\Database\Eloquent\Collection
    {
        return Block::all();
    }

    /**
     * Get active blocks
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Block>
     */
    public function getActiveBlocks(): \Illuminate\Database\Eloquent\Collection
    {
        return Block::where('is_active', true)->get();
    }

    /**
     * Generate a unique slug
     */
    private function generateUniqueSlug(string $baseSlug): string
    {
        $slug = $baseSlug;
        $counter = 1;

        while (Page::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
