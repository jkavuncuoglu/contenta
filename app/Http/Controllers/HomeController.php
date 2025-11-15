<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\PageBuilder\Models\Page;
use App\Domains\Settings\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __construct(
        private readonly BlogController $blogController
    ) {}

    /**
     * Handle the homepage based on landing page setting
     */
    public function index(Request $request): Response
    {
        // First, try to load the "home" page from PageBuilder
        $homePage = Page::where('slug', 'home')
            ->where('status', 'published')
            ->first();

        if ($homePage) {
            return Inertia::render('Page', [
                'page' => [
                    'id' => $homePage->id,
                    'title' => $homePage->title,
                    'slug' => $homePage->slug,
                    'data' => $homePage->data,
                    'meta_title' => $homePage->meta_title,
                    'meta_description' => $homePage->meta_description,
                    'meta_keywords' => $homePage->meta_keywords,
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
                    ->where('status', 'published')
                    ->firstOrFail();

                return Inertia::render('Page', [
                    'page' => [
                        'id' => $page->id,
                        'title' => $page->title,
                        'slug' => $page->slug,
                        'data' => $page->data,
                        'meta_title' => $page->meta_title,
                        'meta_description' => $page->meta_description,
                        'meta_keywords' => $page->meta_keywords,
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
}
