<?php

namespace App\Domains\Settings\SiteSettings\Http\Controllers\Settings;

use App\Domains\Settings\SiteSettings\Http\Requests\Settings\ProfileUpdateRequest;
use App\Domains\Settings\SiteSettings\Services\AvatarService;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private AvatarService $avatarService
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        // Handle avatar upload or URL
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            $this->avatarService->deleteIfLocal($user->avatar);

            // Process and store new avatar
            $data['avatar'] = $this->avatarService->processUpload($request->file('avatar'));
        } elseif (isset($data['avatar']) && is_string($data['avatar'])) {
            // It's a URL, validate and keep it
            if ($this->avatarService->isValidAvatarUrl($data['avatar'])) {
                // Only delete old file if it exists and we're replacing with a URL
                $this->avatarService->deleteIfLocal($user->avatar);
            }
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('settings.profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete avatar
        $this->avatarService->deleteIfLocal($user->avatar);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
