<?php

declare(strict_types=1);

namespace App\Domains\PageBuilder\Actions;

use App\Domains\PageBuilder\Models\Page;
use App\Domains\PageBuilder\Services\PageRenderService;
use Carbon\Carbon;

class PublishPageAction
{
    public function __construct(
        private PageRenderService $pageRenderService
    ) {}

    public function execute(Page $page): Page
    {
        // Validate page has required data
        if (!$page->layout_id) {
            throw new \Exception('Page must have a layout assigned before publishing');
        }

        if (empty($page->data['sections'] ?? [])) {
            throw new \Exception('Page must have at least one section before publishing');
        }

        // Generate HTML from page data
        $renderedHtml = $this->pageRenderService->renderPage($page);

        // Update page with published content
        $page->update([
            'published_html' => $renderedHtml,
            'status' => Page::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return $page->fresh();
    }

    public function unpublish(Page $page): Page
    {
        $page->update([
            'status' => Page::STATUS_DRAFT,
            'published_at' => null,
        ]);

        return $page->fresh();
    }
}