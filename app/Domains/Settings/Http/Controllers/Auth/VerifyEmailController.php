<?php

namespace App\Domains\Settings\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;
use Inertia\Inertia;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request): JsonResponse|RedirectResponse|Response
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $request->wantsJson()
                ? response()->json([
                    'status' => 'success',
                    'message' => 'Email already verified.',
                    'data' => ['email_verified' => true]
                ])
                : redirect()->intended(route('dashboard', absolute: false));
        }

        // Mark the user's email as verified
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));

            // Force update the email_verified_at field
            $user->email_verified_at = now();
            $user->save();

            return $request->wantsJson()
                ? response()->json([
                    'status' => 'success',
                    'message' => 'Email verified successfully.',
                    'data' => ['email_verified' => true]
                ])
                : redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        return $request->wantsJson()
            ? response()->json([
                'status' => 'error',
                'message' => 'Email verification failed.',
            ], 400)
            : back()->with('error', 'Email verification failed.');
    }
}
