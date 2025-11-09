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
     */
    public function createPage(array $data): Page
    {
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return Page::create($data);
    }

    /**
     * Update a page
     */
    public function updatePage(Page $page, array $data): Page
    {
        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $page->update($data);
        return $page->fresh();
    }

    /**
     * Delete a page
     */
    public function deletePage(Page $page): bool
    {
        return $page->delete();
    }

    /**
     * Publish a page
     */
    public function publishPage(Page $page): Page
    {
        $page->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        return $page->fresh();
    }

    /**
     * Unpublish a page
     */
    public function unpublishPage(Page $page): Page
    {
        $page->update([
            'is_published' => false,
        ]);

        return $page->fresh();
    }

    /**
     * Duplicate a page
     */
    public function duplicatePage(Page $page, string $newTitle): Page
    {
        $newPage = $page->replicate();
        $newPage->title = $newTitle;
        $newPage->slug = Str::slug($newTitle);
        $newPage->is_published = false;
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
            ->where('is_published', true)
            ->first();
    }

    /**
     * Get all layouts
     */
    public function getAllLayouts(): \Illuminate\Database\Eloquent\Collection
    {
        return Layout::all();
    }

    /**
     * Get all blocks
     */
    public function getAllBlocks(): \Illuminate\Database\Eloquent\Collection
    {
        return Block::all();
    }

    /**
     * Get active blocks
     */
    public function getActiveBlocks(): \Illuminate\Database\Eloquent\Collection
    {
        return Block::where('is_active', true)->get();
    }
}
