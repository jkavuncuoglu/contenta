<?php

namespace App\Http\Controllers;

use App\Domains\Security\Contracts\TwoFactorAuthenticationServiceInterface;
use App\Http\Requests\TwoFactorAuthenticationRequest;
use App\Http\Requests\TwoFactorRegenerationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class TwoFactorAuthenticationController extends Controller
{
    public function __construct(private TwoFactorAuthenticationServiceInterface $twoFactorService) {}

    /** Generate setup data (QR code + manual secret). */
    public function getSetupData(): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user->hasTwoFactorEnabled()) {
                return response()->json([
                    'message' => 'Two-Factor already enabled.'
                ], 409);
            }
            return response()->json($this->twoFactorService->generateSetupData($user));
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to generate setup data.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /** Enable 2FA after verifying TOTP code. */
    public function enable(TwoFactorAuthenticationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if ($user->hasTwoFactorEnabled()) {
                return response()->json([
                    'message' => 'Two-Factor already enabled.'
                ], 409);
            }
            $code = $request->validated()['code'];
            if (!$this->twoFactorService->enableTwoFactorAuthentication($user, $code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authentication code.',
                    'errors' => ['Invalid authentication code.']
                ], 422);
            }
            $recoveryCodes = $this->twoFactorService->getAvailableRecoveryCodes($user);
            return response()->json([
                'success' => true,
                'message' => 'Two-Factor Authentication enabled successfully.',
                'recovery_codes' => $recoveryCodes,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enable Two-Factor Authentication.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /** Disable 2FA. */
    public function disable(): JsonResponse
    {
        try {
            $user = Auth::user();
            $this->twoFactorService->disableTwoFactorAuthentication($user);
            return response()->json([
                'success' => true,
                'message' => 'Two-Factor Authentication disabled successfully.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to disable Two-Factor Authentication.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /** One-time retrieval of recovery codes (empty after first view). */
    public function getRecoveryCodes(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user->hasTwoFactorEnabled()) {
                return response()->json([
                    'message' => 'Two-Factor Authentication is not enabled.',
                    'errors' => ['Two-Factor Authentication is not enabled.']
                ], 422);
            }
            $hasViewed = $user->hasViewedRecoveryCodes();
            $codes = [];
            if (!$hasViewed) {
                $codes = $this->twoFactorService->getAvailableRecoveryCodes($user);
                $user->markRecoveryCodesAsViewed();
            }
            return response()->json([
                'recovery_codes' => $codes,
                'has_viewed' => $hasViewed || count($codes) === 0,
                'show_warning' => $user->shouldShowRecoveryCodesWarning(),
                'available_count' => $user->getAvailableRecoveryCodesCount(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve recovery codes.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /** Download recovery codes (only if not yet viewed). */
    public function downloadRecoveryCodes(): \Illuminate\Http\Response
    {
        $user = Auth::user();
        if (!$user->hasTwoFactorEnabled()) {
            abort(422, 'Two-Factor Authentication is not enabled.');
        }
        if ($user->hasViewedRecoveryCodes()) {
            abort(410, 'Recovery codes already viewed. Regenerate to view again.');
        }
        $recoveryCodes = $this->twoFactorService->getAvailableRecoveryCodes($user);
        $content = "Recovery Codes for " . config('app.name') . "\n";
        $content .= "Generated on: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= "User: " . $user->email . "\n\n";
        $content .= "Keep these codes safe. Each code can only be used once.\n\n";
        foreach ($recoveryCodes as $i => $code) {
            $content .= ($i + 1) . '. ' . $code . "\n";
        }
        // Mark as viewed after offering download
        $user->markRecoveryCodesAsViewed();
        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="recovery-codes-' . now()->format('Y-m-d') . '.txt"'
        ]);
    }

    /** Request regeneration via password + 2FA code. */
    public function requestRegeneration(TwoFactorRegenerationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validated = $request->validated();
            if ($this->twoFactorService->requestRecoveryCodesRegeneration($user, $validated['password'], $validated['code'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Regeneration email sent. Check your inbox to confirm.'
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => 'Invalid password or authentication code.',
                'errors' => ['Invalid password or authentication code.']
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to request regeneration.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /** Confirm regeneration using emailed token. */
    public function confirmRegeneration(Request $request): JsonResponse|\Inertia\Response
    {
        try {
            $token = $request->query('token');
            if (!$token) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Regeneration token is required.',
                        'errors' => ['Regeneration token is required.']
                    ], 422);
                }
                abort(422, 'Regeneration token is required.');
            }
            $user = Auth::user();
            $newRecoveryCodes = $this->twoFactorService->confirmRecoveryCodesRegeneration($user, $token);
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Recovery codes regenerated successfully.',
                    'recovery_codes' => $newRecoveryCodes
                ]);
            }
            return Inertia::render('two-factor/RecoveryCodesRegenerated', [
                'recoveryCodes' => $newRecoveryCodes,
            ]);
        } catch (\InvalidArgumentException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => [$e->getMessage()]
                ], 422);
            }
            abort(422, $e->getMessage());
        } catch (\Throwable $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to confirm regeneration.',
                    'errors' => [$e->getMessage()]
                ], 500);
            }
            abort(500, 'Failed to confirm regeneration.');
        }
    }

    /** Get current 2FA status. */
    public function getStatus(): JsonResponse
    {
        $user = Auth::user();
        return response()->json([
            'enabled' => $user->hasTwoFactorEnabled(),
            'confirmed_at' => $user->two_factor_confirmed_at,
            'recovery_codes_count' => $user->getAvailableRecoveryCodesCount(),
            'show_warning' => $user->shouldShowRecoveryCodesWarning(),
            'has_viewed_codes' => $user->hasViewedRecoveryCodes(),
        ]);
    }

    /** Consume a recovery code (marks as used). */
    public function useRecoveryCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string']
        ]);
        $user = Auth::user();
        if (!$user->hasTwoFactorEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Two-Factor not enabled.'
            ], 422);
        }
        $code = $request->input('code');
        try {
            if (!$this->twoFactorService->validateRecoveryCode($user, $code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or already used recovery code.'
                ], 422);
            }
            return response()->json([
                'success' => true,
                'message' => 'Recovery code accepted.',
                'remaining' => $user->getAvailableRecoveryCodesCount(),
                'show_warning' => $user->shouldShowRecoveryCodesWarning(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to use recovery code.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}
