<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Services\MarkdownRenderServiceContract;
use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\Settings\Models\Setting;
use App\Http\Controllers\BlogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly BlogController $blogController,
        private readonly MarkdownRenderServiceContract $markdownRenderService
    ) {}

    /**
     * Handle the homepage based on landing page setting
     */
    public function index(Request $request): Response
    {
        // First, try to load the "home" or "/" page
        $homePage = Page::whereIn('slug', ['home', '/'])
            ->where('status', Page::STATUS_PUBLISHED)
            ->first();

        if ($homePage) {
            $contentHtml = $this->getCachedOrRenderHtml($homePage);

            return Inertia::render('Page', [
                'page' => [
                    'id' => $homePage->id,
                    'title' => $homePage->title,
                    'slug' => $homePage->slug,
                    'content_html' => $contentHtml,
                    'meta_title' => $homePage->meta_title,
                    'meta_description' => $homePage->meta_description,
                    'meta_keywords' => $homePage->meta_keywords,
                    'schema_data' => $homePage->schema_data,
                ],
            ]);
        }

        // Otherwise, check landing page setting
        $landingPage = Setting::get('site', 'site_landing_page', 'blog');

        // If landing page is blog, use the blog controller
        if ($landingPage === 'blog') {
            return $this->blogController->index($request);
        }

        // If landing page is a specific page ID, load that page
        if (is_numeric($landingPage)) {
            try {
                $page = Page::where('id', $landingPage)
                    ->where('status', Page::STATUS_PUBLISHED)
                    ->firstOrFail();

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
                ]);
            } catch (\Exception $e) {
                // If page not found, fallback to blog
                return $this->blogController->index($request);
            }
        }

        // Fallback to default welcome page
        $siteTitle = Setting::get('site', 'site_title', 'Welcome');
        $siteTagline = Setting::get('site', 'site_tagline', '');

        return Inertia::render('Welcome', [
            'siteTitle' => $siteTitle,
            'siteTagline' => $siteTagline,
        ]);
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
