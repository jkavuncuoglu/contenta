<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Http\Controllers;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\Settings\Models\Setting;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display the blog page with latest posts
     */
    public function index(Request $request): Response
    {
        $posts = Post::with(['author'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        $siteTitle = Setting::get('site', 'site_title', 'Blog');
        $siteTagline = Setting::get('site', 'site_tagline', '');

        return Inertia::render('Blog', [
            'posts' => $posts,
            'siteTitle' => $siteTitle,
            'siteTagline' => $siteTagline,
        ]);
    }

    /**
     * Display a single blog post
     */
    public function show(string $slug): Response
    {
        $post = Post::with(['author', 'comments' => function ($query) {
            $query->where('status', 'approved')->orderBy('created_at', 'asc');
        }])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $siteTitle = Setting::get('site', 'site_title', 'Blog');

        return Inertia::render('BlogPost', [
            'post' => $post,
            'siteTitle' => $siteTitle,
        ]);
    }
}
