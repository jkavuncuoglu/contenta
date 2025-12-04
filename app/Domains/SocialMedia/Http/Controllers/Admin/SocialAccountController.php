<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Http\Controllers\Admin;

use App\Domains\SocialMedia\Http\Requests\UpdateSocialAccountRequest;
use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Services\OAuthServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class SocialAccountController extends Controller
{
    public function __construct(
        protected OAuthServiceContract $oauthService
    ) {
        $this->middleware('permission:view social accounts')->only(['index', 'show']);
        $this->middleware('permission:edit social accounts')->only(['edit', 'update']);
        $this->middleware('permission:disconnect social accounts')->only(['destroy']);
        $this->middleware('permission:refresh social tokens')->only(['verify']);
    }

    /**
     * Display a listing of all social accounts.
     */
    public function index(): Response
    {
        $accounts = SocialAccount::with(['socialPosts' => function ($query) {
            $query->where('status', 'published')
                ->orderBy('published_at', 'desc')
                ->limit(5);
        }])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'platform' => $account->platform,
                    'platform_username' => $account->platform_username,
                    'platform_display_name' => $account->platform_display_name,
                    'is_active' => $account->is_active,
                    'auto_post_enabled' => $account->auto_post_enabled,
                    'auto_post_mode' => $account->auto_post_mode,
                    'scheduled_post_time' => $account->scheduled_post_time,
                    'token_expires_at' => $account->token_expires_at?->toIso8601String(),
                    'last_synced_at' => $account->last_synced_at?->toIso8601String(),
                    'created_at' => $account->created_at->toIso8601String(),
                    'recent_posts_count' => $account->socialPosts->count(),
                ];
            });

        return Inertia::render('admin/social-media/accounts/Index', [
            'accounts' => $accounts,
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Social Media', 'href' => route('admin.social-media.accounts.index')],
                ['label' => 'Accounts'],
            ],
        ]);
    }

    /**
     * Display the specified social account.
     */
    public function show(SocialAccount $account): Response
    {
        $account->load([
            'socialPosts' => function ($query) {
                $query->orderBy('scheduled_at', 'desc')->limit(10);
            },
        ]);

        return Inertia::render('admin/social-media/accounts/Show', [
            'account' => [
                'id' => $account->id,
                'platform' => $account->platform,
                'platform_username' => $account->platform_username,
                'platform_display_name' => $account->platform_display_name,
                'platform_account_id' => $account->platform_account_id,
                'is_active' => $account->is_active,
                'auto_post_enabled' => $account->auto_post_enabled,
                'auto_post_mode' => $account->auto_post_mode,
                'scheduled_post_time' => $account->scheduled_post_time,
                'platform_settings' => $account->platform_settings,
                'token_expires_at' => $account->token_expires_at?->toIso8601String(),
                'last_synced_at' => $account->last_synced_at?->toIso8601String(),
                'created_at' => $account->created_at->toIso8601String(),
                'posts' => $account->socialPosts,
            ],
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Social Media', 'href' => route('admin.social-media.accounts.index')],
                ['label' => 'Accounts', 'href' => route('admin.social-media.accounts.index')],
                ['label' => $account->platform_display_name ?? ucfirst($account->platform)],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified social account.
     */
    public function edit(SocialAccount $account): Response
    {
        return Inertia::render('admin/social-media/accounts/Edit', [
            'account' => [
                'id' => $account->id,
                'platform' => $account->platform,
                'platform_username' => $account->platform_username,
                'platform_display_name' => $account->platform_display_name,
                'is_active' => $account->is_active,
                'auto_post_enabled' => $account->auto_post_enabled,
                'auto_post_mode' => $account->auto_post_mode,
                'scheduled_post_time' => $account->scheduled_post_time,
                'platform_settings' => $account->platform_settings,
            ],
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'href' => route('admin.dashboard')],
                ['label' => 'Social Media', 'href' => route('admin.social-media.accounts.index')],
                ['label' => 'Accounts', 'href' => route('admin.social-media.accounts.index')],
                ['label' => 'Edit'],
            ],
        ]);
    }

    /**
     * Update the specified social account.
     */
    public function update(UpdateSocialAccountRequest $request, SocialAccount $account): RedirectResponse
    {
        $validated = $request->validated();

        $account->update([
            'is_active' => $validated['is_active'] ?? $account->is_active,
            'auto_post_enabled' => $validated['auto_post_enabled'] ?? false,
            'auto_post_mode' => $validated['auto_post_mode'] ?? 'immediate',
            'scheduled_post_time' => $validated['scheduled_post_time'] ?? null,
            'platform_settings' => $validated['platform_settings'] ?? $account->platform_settings,
        ]);

        return redirect()
            ->route('admin.social-media.accounts.index')
            ->with('success', 'Account settings updated successfully!');
    }

    /**
     * Remove the specified social account.
     */
    public function destroy(SocialAccount $account): RedirectResponse
    {
        try {
            $this->oauthService->disconnect($account);

            return redirect()
                ->route('admin.social-media.accounts.index')
                ->with('success', 'Account disconnected successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Failed to disconnect account: {$e->getMessage()}");
        }
    }

    /**
     * Verify the social account connection.
     */
    public function verify(SocialAccount $account): RedirectResponse
    {
        try {
            $isValid = $this->oauthService->verifyConnection($account);

            if ($isValid) {
                $account->update(['last_synced_at' => now()]);

                return back()->with('success', 'Account connection verified successfully!');
            }

            return back()->with('warning', 'Account connection could not be verified. You may need to reconnect.');
        } catch (\Exception $e) {
            return back()->with('error', "Verification failed: {$e->getMessage()}");
        }
    }
}
