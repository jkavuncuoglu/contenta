<?php

declare(strict_types=1);

namespace App\Domains\Calendar\Http\Controllers\Admin;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\SocialMedia\Models\SocialPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for unified calendar view (blog posts + social posts).
 */
class CalendarController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view calendar')->only(['index', 'data']);
    }

    /**
     * Display the unified calendar page.
     */
    public function index(): Response
    {
        return Inertia::render('admin/calendar/Index', [
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Calendar'],
            ],
        ]);
    }

    /**
     * Get calendar data (blog posts + social posts).
     *
     * Returns events in format:
     * [
     *   {
     *     id: 1,
     *     type: 'blog',
     *     title: 'Blog Post Title',
     *     status: 'published',
     *     date: '2025-12-04T10:00:00Z',
     *     author: 'John Doe',
     *     url: '/admin/posts/1/edit'
     *   },
     *   {
     *     id: 2,
     *     type: 'social',
     *     platform: 'twitter',
     *     title: 'Post content preview...',
     *     status: 'scheduled',
     *     date: '2025-12-04T14:00:00Z',
     *     account: '@myaccount',
     *     url: '/admin/social-media/posts/2/edit'
     *   }
     * ]
     */
    public function data(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'types' => 'nullable|string', // 'blog,social'
            'platform' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $types = isset($validated['types']) ? explode(',', $validated['types']) : ['blog', 'social'];
        $events = [];

        // Blog Posts
        if (in_array('blog', $types)) {
            $blogQuery = Post::whereBetween('published_at', [
                $validated['start_date'],
                $validated['end_date'],
            ])
                ->with('author');

            // Filter by status if provided
            if (isset($validated['status'])) {
                $blogQuery->where('status', $validated['status']);
            }

            $blogPosts = $blogQuery->get()
                ->map(fn ($post) => [
                    'id' => $post->id,
                    'type' => 'blog',
                    'title' => $post->title,
                    'status' => $post->status,
                    'date' => $post->published_at?->toIso8601String(),
                    'author' => $post->author->name,
                    'url' => route('admin.posts.edit', $post->id),
                    'color' => $this->getColorForBlogStatus($post->status),
                ]);

            $events = array_merge($events, $blogPosts->toArray());
        }

        // Social Posts
        if (in_array('social', $types)) {
            $socialQuery = SocialPost::where(function ($query) use ($validated) {
                $query->whereBetween('scheduled_at', [
                    $validated['start_date'],
                    $validated['end_date'],
                ])
                    ->orWhereBetween('published_at', [
                        $validated['start_date'],
                        $validated['end_date'],
                    ]);
            })
                ->with('socialAccount');

            // Filter by platform if provided
            if (isset($validated['platform'])) {
                $socialQuery->whereHas('socialAccount', fn ($q) => $q->where('platform', $validated['platform']));
            }

            // Filter by status if provided
            if (isset($validated['status'])) {
                $socialQuery->where('status', $validated['status']);
            }

            $socialPosts = $socialQuery->get()
                ->map(fn ($post) => [
                    'id' => $post->id,
                    'type' => 'social',
                    'platform' => $post->socialAccount->platform,
                    'title' => Str::limit($post->content, 50),
                    'content' => Str::limit($post->content, 100),
                    'status' => $post->status,
                    'date' => ($post->published_at ?? $post->scheduled_at)?->toIso8601String(),
                    'account' => $post->socialAccount->platform_username,
                    'url' => route('admin.social-media.posts.edit', $post->id),
                    'color' => $this->getColorForSocialPlatform($post->socialAccount->platform),
                ]);

            $events = array_merge($events, $socialPosts->toArray());
        }

        // Sort by date
        usort($events, fn ($a, $b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));

        return response()->json(['data' => $events]);
    }

    /**
     * Get color for blog post status.
     */
    protected function getColorForBlogStatus(string $status): string
    {
        return match ($status) {
            'published' => '#10b981', // green
            'draft' => '#6b7280', // gray
            'scheduled' => '#3b82f6', // blue
            default => '#6b7280',
        };
    }

    /**
     * Get color for social platform.
     */
    protected function getColorForSocialPlatform(string $platform): string
    {
        return match ($platform) {
            'twitter' => '#1DA1F2',
            'facebook' => '#1877F2',
            'linkedin' => '#0A66C2',
            'instagram' => '#E4405F',
            'pinterest' => '#BD081C',
            'tiktok' => '#000000',
            default => '#6b7280',
        };
    }
}
