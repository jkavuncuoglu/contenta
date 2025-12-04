<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Http\Controllers\Admin;

use App\Domains\SocialMedia\Constants\PostStatus;
use App\Domains\SocialMedia\Http\Requests\StoreSocialPostRequest;
use App\Domains\SocialMedia\Http\Requests\UpdateSocialPostRequest;
use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Models\SocialPost;
use App\Domains\SocialMedia\Services\SocialMediaServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SocialPostController extends Controller
{
    public function __construct(
        protected SocialMediaServiceContract $socialMediaService
    ) {
        $this->middleware('permission:view social posts')->only(['index', 'show', 'scheduled']);
        $this->middleware('permission:create social posts')->only(['create', 'store']);
        $this->middleware('permission:edit social posts')->only(['edit', 'update']);
        $this->middleware('permission:delete social posts')->only(['destroy']);
        $this->middleware('permission:publish social posts')->only(['publish', 'cancel', 'retry']);
    }

    /**
     * Display a listing of social posts.
     */
    public function index(Request $request): Response
    {
        $query = SocialPost::with('socialAccount')
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('platform')) {
            $query->whereHas('socialAccount', fn ($q) => $q->where('platform', $request->platform));
        }

        if ($request->filled('source_type')) {
            $query->where('source_type', $request->source_type);
        }

        $posts = $query->paginate(20)->through(fn ($post) => [
            'id' => $post->id,
            'content' => $post->content,
            'status' => $post->status,
            'source_type' => $post->source_type,
            'scheduled_at' => $post->scheduled_at?->toIso8601String(),
            'published_at' => $post->published_at?->toIso8601String(),
            'platform_permalink' => $post->platform_permalink,
            'error_message' => $post->error_message,
            'retry_count' => $post->retry_count,
            'account' => [
                'id' => $post->socialAccount->id,
                'platform' => $post->socialAccount->platform,
                'platform_username' => $post->socialAccount->platform_username,
            ],
            'created_at' => $post->created_at->toIso8601String(),
        ]);

        $accounts = SocialAccount::where('is_active', true)
            ->orderBy('platform')
            ->get(['id', 'platform', 'platform_username']);

        return Inertia::render('admin/social-media/posts/Index', [
            'posts' => $posts,
            'accounts' => $accounts,
            'filters' => $request->only(['status', 'platform', 'source_type']),
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Social Media', 'href' => route('admin.social-media.accounts.index')],
                ['label' => 'Posts'],
            ],
        ]);
    }

    /**
     * Show the form for creating a new post.
     */
    public function create(): Response
    {
        $accounts = SocialAccount::where('is_active', true)
            ->orderBy('platform')
            ->get()
            ->map(fn ($account) => [
                'id' => $account->id,
                'platform' => $account->platform,
                'platform_username' => $account->platform_username,
                'platform_display_name' => $account->platform_display_name,
            ]);

        return Inertia::render('admin/social-media/posts/Create', [
            'accounts' => $accounts,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Social Media', 'href' => route('admin.social-media.accounts.index')],
                ['label' => 'Posts', 'href' => route('admin.social-media.posts.index')],
                ['label' => 'Create'],
            ],
        ]);
    }

    /**
     * Store a newly created post.
     */
    public function store(StoreSocialPostRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();

        try {
            $post = $this->socialMediaService->createPost($validated);

            // If scheduled_at is provided and status is draft, schedule it
            if ($validated['status'] === PostStatus::DRAFT && isset($validated['scheduled_at'])) {
                $this->socialMediaService->schedulePost($post, new \DateTime($validated['scheduled_at']));
            }

            // If status is set to publish immediately
            if ($validated['status'] === PostStatus::SCHEDULED && ! isset($validated['scheduled_at'])) {
                $this->socialMediaService->publishPost($post);
            }

            return redirect()
                ->route('admin.social-media.posts.index')
                ->with('success', 'Post created successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', "Failed to create post: {$e->getMessage()}");
        }
    }

    /**
     * Display the specified post.
     */
    public function show(SocialPost $post): Response
    {
        $post->load('socialAccount');

        return Inertia::render('admin/social-media/posts/Show', [
            'post' => [
                'id' => $post->id,
                'content' => $post->content,
                'media_urls' => $post->media_urls,
                'link_url' => $post->link_url,
                'status' => $post->status,
                'source_type' => $post->source_type,
                'scheduled_at' => $post->scheduled_at?->toIso8601String(),
                'published_at' => $post->published_at?->toIso8601String(),
                'platform_post_id' => $post->platform_post_id,
                'platform_permalink' => $post->platform_permalink,
                'error_message' => $post->error_message,
                'retry_count' => $post->retry_count,
                'account' => [
                    'id' => $post->socialAccount->id,
                    'platform' => $post->socialAccount->platform,
                    'platform_username' => $post->socialAccount->platform_username,
                    'platform_display_name' => $post->socialAccount->platform_display_name,
                ],
                'created_at' => $post->created_at->toIso8601String(),
                'updated_at' => $post->updated_at->toIso8601String(),
            ],
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Social Media', 'href' => route('admin.social-media.accounts.index')],
                ['label' => 'Posts', 'href' => route('admin.social-media.posts.index')],
                ['label' => 'View'],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(SocialPost $post): Response
    {
        $post->load('socialAccount');

        $accounts = SocialAccount::where('is_active', true)
            ->orderBy('platform')
            ->get()
            ->map(fn ($account) => [
                'id' => $account->id,
                'platform' => $account->platform,
                'platform_username' => $account->platform_username,
                'platform_display_name' => $account->platform_display_name,
            ]);

        return Inertia::render('admin/social-media/posts/Edit', [
            'post' => [
                'id' => $post->id,
                'social_account_id' => $post->social_account_id,
                'content' => $post->content,
                'media_urls' => $post->media_urls,
                'link_url' => $post->link_url,
                'status' => $post->status,
                'scheduled_at' => $post->scheduled_at?->format('Y-m-d\TH:i'),
                'account' => [
                    'id' => $post->socialAccount->id,
                    'platform' => $post->socialAccount->platform,
                    'platform_username' => $post->socialAccount->platform_username,
                ],
            ],
            'accounts' => $accounts,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Social Media', 'href' => route('admin.social-media.accounts.index')],
                ['label' => 'Posts', 'href' => route('admin.social-media.posts.index')],
                ['label' => 'Edit'],
            ],
        ]);
    }

    /**
     * Update the specified post.
     */
    public function update(UpdateSocialPostRequest $request, SocialPost $post): RedirectResponse
    {
        try {
            $validated = $request->validated();

            $this->socialMediaService->updatePost($post, $validated);

            return redirect()
                ->route('admin.social-media.posts.index')
                ->with('success', 'Post updated successfully!');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', "Failed to update post: {$e->getMessage()}");
        }
    }

    /**
     * Remove the specified post.
     */
    public function destroy(SocialPost $post): RedirectResponse
    {
        try {
            $this->socialMediaService->deletePost($post);

            return redirect()
                ->route('admin.social-media.posts.index')
                ->with('success', 'Post deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to delete post: {$e->getMessage()}");
        }
    }

    /**
     * Publish a post immediately.
     */
    public function publish(SocialPost $post): RedirectResponse
    {
        try {
            $this->socialMediaService->publishPost($post);

            return back()->with('success', 'Post published successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to publish post: {$e->getMessage()}");
        }
    }

    /**
     * Cancel a scheduled post.
     */
    public function cancel(SocialPost $post): RedirectResponse
    {
        if ($post->status !== PostStatus::SCHEDULED) {
            return back()->with('error', 'Can only cancel scheduled posts');
        }

        try {
            $post->update([
                'status' => PostStatus::DRAFT,
                'scheduled_at' => null,
            ]);

            return back()->with('success', 'Post cancelled and reverted to draft');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to cancel post: {$e->getMessage()}");
        }
    }

    /**
     * Retry a failed post.
     */
    public function retry(SocialPost $post): RedirectResponse
    {
        if ($post->status !== PostStatus::FAILED) {
            return back()->with('error', 'Can only retry failed posts');
        }

        if ($post->retry_count >= 3) {
            return back()->with('error', 'Maximum retry attempts reached');
        }

        try {
            $this->socialMediaService->publishPost($post);

            return back()->with('success', 'Post retry initiated');
        } catch (\Exception $e) {
            return back()->with('error', "Retry failed: {$e->getMessage()}");
        }
    }

    /**
     * Check for scheduling conflicts (API endpoint).
     */
    public function checkConflicts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'social_account_id' => 'required|exists:social_accounts,id',
            'scheduled_at' => 'required|date',
        ]);

        $account = SocialAccount::findOrFail($validated['social_account_id']);
        $scheduledAt = new \DateTime($validated['scheduled_at']);

        $conflicts = $this->socialMediaService->checkConflicts($account, $scheduledAt);

        return response()->json($conflicts);
    }

    /**
     * Get scheduled posts (API endpoint).
     */
    public function scheduled(Request $request): JsonResponse
    {
        $posts = $this->socialMediaService->getScheduledPosts($request->input('per_page', 20));

        return response()->json($posts);
    }
}
