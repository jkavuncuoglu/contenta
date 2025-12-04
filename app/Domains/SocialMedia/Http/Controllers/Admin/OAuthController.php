<?php

declare(strict_types=1);

namespace App\Domains\SocialMedia\Http\Controllers\Admin;

use App\Domains\SocialMedia\Models\SocialAccount;
use App\Domains\SocialMedia\Services\OAuthServiceContract;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OAuthController extends Controller
{
    public function __construct(
        protected OAuthServiceContract $oauthService
    ) {
        // Permissions are handled at route level
    }

    /**
     * Start OAuth authorization flow.
     */
    public function authorize(Request $request, string $platform): RedirectResponse
    {
        try {
            $authUrl = $this->oauthService->getAuthorizationUrl($platform);

            return redirect()->away($authUrl);
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.social-media.accounts.index')
                ->with('error', "Failed to start OAuth: {$e->getMessage()}");
        }
    }

    /**
     * Handle OAuth callback.
     */
    public function callback(Request $request, string $platform): RedirectResponse
    {
        try {
            $code = $request->input('code');
            $state = $request->input('state');

            if (! $code || ! $state) {
                throw new \Exception('Missing OAuth parameters');
            }

            $account = $this->oauthService->handleCallback($platform, $code, $state);

            return redirect()
                ->route('admin.social-media.accounts.index')
                ->with('success', ucfirst($platform).' account connected successfully!');
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.social-media.accounts.index')
                ->with('error', "OAuth failed: {$e->getMessage()}");
        }
    }

    /**
     * Refresh an expired token.
     */
    public function refresh(Request $request, int $accountId): RedirectResponse
    {
        try {
            $account = SocialAccount::findOrFail($accountId);
            $this->oauthService->refreshToken($account);

            return back()->with('success', 'Token refreshed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Token refresh failed: {$e->getMessage()}");
        }
    }

    /**
     * Disconnect a social account.
     */
    public function disconnect(Request $request, int $accountId): RedirectResponse
    {
        try {
            $account = SocialAccount::findOrFail($accountId);
            $this->oauthService->disconnect($account);

            return redirect()
                ->route('admin.social-media.accounts.index')
                ->with('success', 'Account disconnected successfully!');
        } catch (\Exception $e) {
            return back()->with('error', "Disconnect failed: {$e->getMessage()}");
        }
    }
}
