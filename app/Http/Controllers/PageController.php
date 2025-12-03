<?php

namespace App\Http\Controllers;

use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Services\MarkdownRenderServiceContract;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    public function __construct(
        private MarkdownRenderServiceContract $markdownRenderService
    ) {}

    /**
     * Display the specified page.
     */
    public function show(Request $request, string $slug): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $page = Page::where('slug', $slug)
                ->where('status', Page::STATUS_PUBLISHED)
                ->firstOrFail();

            // Get content from storage and render
            $contentHtml = $this->getCachedOrRenderHtml($page);

            return Inertia::render('Page', [
                'page' => [
                    'id' => $page->id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'content_html' => $contentHtml,
                    'meta_title' => $page->meta_title,
                    'meta_description' => $page->meta_description,
                    'meta_keywords' => $page->meta_keywords,
                    'schema_data' => $page->schema_data,
                ],
            ])->toResponse($request);
        } catch (ModelNotFoundException $e) {
            return Inertia::render('Page', [
                'page' => null,
                'message' => 'Page not found',
            ])->toResponse($request)->setStatusCode(404);
        }
    }

    /**
     * Get cached HTML or render on-the-fly
     */
    private function getCachedOrRenderHtml(Page $page): string
    {
        $cacheKey = 'page.html.' . $page->id;

        return Cache::remember($cacheKey, 3600, function () use ($page) {
            $content = $page->content;

            if (!$content) {
                return '<div class="text-center text-gray-500 py-12">No content available</div>';
            }

            return $this->markdownRenderService->render($content->getContent());
        });
    }
}
