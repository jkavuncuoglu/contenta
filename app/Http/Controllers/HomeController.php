<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\PageBuilder\Models\Page;
use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\Settings\Models\Setting;
use App\Http\Controllers\BlogController;
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

                $siteTitle = Setting::get('site', 'site_title', 'Welcome');

                return Inertia::render('Page', [
                    'page' => $page,
                    'siteTitle' => $siteTitle,
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
