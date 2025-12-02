<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Actions;

use App\Domains\PageBuilder\Models\Page;
use App\Domains\PageBuilder\Services\PageRenderService;
use App\Domains\PageBuilder\Services\MarkdownRenderServiceContract;
use Carbon\Carbon;

class PublishPageAction
{
    public function __construct(
        private PageRenderService $pageRenderService,
        private MarkdownRenderServiceContract $markdownRenderService
    ) {}

    public function execute(Page $page): Page
    {
        // Validate based on content type
        if ($page->isMarkdown()) {
            $this->validateMarkdownPage($page);
        } else {
            $this->validateLegacyPage($page);
        }

        // Update page status (HTML will be rendered on-the-fly with caching)
        $page->update([
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        // Clear any existing cache for this page
        $this->clearPageCache($page);

        $freshPage = $page->fresh();
        if (!$freshPage) {
            throw new \Exception('Failed to refresh page after publishing');
        }

        return $freshPage;
    }

    /**
     * Clear cached HTML for a page
     */
    private function clearPageCache(Page $page): void
    {
        $cacheKeys = [
            'page.html.' . $page->id,
            'page.html.slug.' . $page->slug,
        ];

        foreach ($cacheKeys as $key) {
            \Cache::forget($key);
        }
    }

    private function validateLegacyPage(Page $page): void
    {
        if (!$page->layout_id) {
            throw new \Exception('Page must have a layout assigned before publishing');
        }

        if (empty($page->data['sections'] ?? [])) {
            throw new \Exception('Page must have at least one section before publishing');
        }
    }

    private function validateMarkdownPage(Page $page): void
    {
        if (empty($page->markdown_content)) {
            throw new \Exception('Page must have markdown content before publishing');
        }

        // Try to validate by attempting to render
        // If rendering fails, we'll still allow publishing - it will just cache the error
        // This prevents validation issues from blocking publishing
        try {
            $this->markdownRenderService->renderPage($page, true);
        } catch (\Exception $e) {
            // Log the validation warning but don't block publishing
            \Log::warning('Page published with potential rendering issues', [
                'page_id' => $page->id,
                'page_title' => $page->title,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function unpublish(Page $page): Page
    {
        $page->update([
            'status' => Page::STATUS_DRAFT,
            'published_at' => null,
        ]);

        // Clear cached HTML
        $this->clearPageCache($page);

        $freshPage = $page->fresh();
        if (!$freshPage) {
            throw new \Exception('Failed to refresh page after unpublishing');
        }

        return $freshPage;
    }
}