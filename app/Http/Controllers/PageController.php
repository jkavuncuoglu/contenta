<?php

namespace App\Http\Controllers;

use App\Domains\PageBuilder\Models\Page;
use App\Domains\PageBuilder\Services\MarkdownRenderServiceContract;
use App\Domains\PageBuilder\Services\PageRenderService;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function __construct(
        private MarkdownRenderServiceContract $markdownRenderService,
        private PageRenderService $pageRenderService
    ) {}

    /**
     * Display the specified page.
     */
    public function show(Request $request, string $slug): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $page = Page::where('slug', $slug)
                ->where('status', 'published')
                ->firstOrFail();

            // Get cached or render HTML on-the-fly
            $contentHtml = $this->getCachedOrRenderHtml($page);

            return Inertia::render('Page', [
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    // keep legacy data structure if present
                    'data' => $page->data,
                    // Cached or on-the-fly rendered HTML
                    'content_html' => $contentHtml,
                    // original markdown source (if available)
                    'content_markdown' => $page->markdown_content ?? null,
                    'meta_title' => $page->meta_title,
                    'meta_description' => $page->meta_description,
                    'meta_keywords' => $page->meta_keywords,
                ],
            ])->toResponse($request);
        } catch (ModelNotFoundException $e) {
            // Return a proper Inertia HTML response (status 404) so Inertia client doesn't receive plain JSON
            return Inertia::render('Page', [
                'page' => null,
                'message' => 'Page not found',
            ])->toResponse($request)->setStatusCode(404);
        }
    }

    /**
     * Get cached HTML or render on-the-fly
     */
    private function getCachedOrRenderHtml(Page $page): ?string
    {
        $cacheKey = 'page.html.' . $page->id;

        return Cache::remember($cacheKey, 3600, function () use ($page) {
            if ($page->isMarkdown()) {
                return $this->markdownRenderService->renderPage($page);
            } else {
                return $this->pageRenderService->renderPage($page);
            }
        });
    }
}
