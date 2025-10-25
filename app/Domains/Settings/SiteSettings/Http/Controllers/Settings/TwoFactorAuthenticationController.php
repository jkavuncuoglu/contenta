<?php

namespace App\Domains\Settings\SiteSettings\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use OTPHP\TOTP;

class TwoFactorAuthenticationController extends Controller
{
    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Security', [
            'twoFactorEnabled' => $user->hasTwoFactorEnabled(),
        ]);
    }

    /**
     * Get QR code and manual entry for 2FA setup.
     */
    public function getSetupData(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasTwoFactorEnabled()) {
                return response()->json([
                    'message' => 'Two-Factor Authentication is already enabled.'
                ], 409);
            }

            // Generate a new secret
            $secret = bin2hex(random_bytes(32));

            // Store temporarily (will be saved permanently on enable)
            $user->forceFill([
                'two_factor_secret' => encrypt($secret),
                'two_factor_confirmed_at' => null,
            ])->save();

            // Generate TOTP
            $totp = TOTP::create($secret);
            $totp->setLabel($user->email);
            $totp->setIssuer(config('app.name'));

            // Generate QR code
            $qrCode = new QrCode($totp->getProvisioningUri());
            $writer = new PngWriter();
            $qrCodeData = base64_encode($writer->write($qrCode)->getString());

            return response()->json([
                'qrCode' => $qrCodeData,
                'manualEntry' => $secret,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to generate setup data.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Enable 2FA after verifying TOTP code.
     */
    public function enable(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if ($user->hasTwoFactorEnabled()) {
                return response()->json([
                    'message' => 'Two-Factor Authentication is already enabled.'
                ], 409);
            }

            $request->validate([
                'code' => 'required|string|size:6',
            ]);

            $code = $request->input('code');
            $secret = $user->two_factor_secret ? decrypt($user->two_factor_secret) : null;

            if (!$secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'No setup data found. Please start the setup process.',
                    'errors' => ['No setup data found.']
                ], 422);
            }

            // Verify the code
            $totp = TOTP::create($secret);
            if (!$totp->verify($code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authentication code.',
                    'errors' => ['Invalid authentication code.']
                ], 422);
            }

            // Generate recovery codes
            $recoveryCodes = collect(range(1, 10))
                ->map(fn () => bin2hex(random_bytes(8)))
                ->all();

            // Enable 2FA
            $user->forceFill([
                'two_factor_confirmed_at' => now(),
                'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
                'two_factor_used_recovery_codes' => [],
                'two_factor_recovery_codes_viewed_at' => null,
            ])->save();

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

    /**
     * Disable 2FA.
     */
    public function disable(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $user->forceFill([
                'two_factor_secret' => null,
                'two_factor_confirmed_at' => null,
                'two_factor_recovery_codes' => null,
                'two_factor_used_recovery_codes' => [],
                'two_factor_recovery_codes_viewed_at' => null,
            ])->save();

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

    /**
     * Get recovery codes (only shown once until regenerated).
     */
    public function getRecoveryCodes(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            if (!$user->hasTwoFactorEnabled()) {
                return response()->json([
                    'message' => 'Two-Factor Authentication is not enabled.',
                    'errors' => ['Two-Factor Authentication is not enabled.']
                ], 422);
            }

            $hasViewed = $user->hasViewedRecoveryCodes();
            $codes = [];

            if (!$hasViewed) {
                $codes = $user->two_factor_recovery_codes
                    ? json_decode(decrypt($user->two_factor_recovery_codes), true) ?? []
                    : [];
            }

            $availableCount = $user->getAvailableRecoveryCodesCount();

            return response()->json([
                'recovery_codes' => $codes,
                'has_viewed' => $hasViewed,
                'show_warning' => $user->shouldShowRecoveryCodesWarning(),
                'available_count' => $availableCount,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to retrieve recovery codes.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Download recovery codes as text file.
     */
    public function downloadRecoveryCodes(Request $request): \Illuminate\Http\Response
    {
        $user = $request->user();

        if (!$user->hasTwoFactorEnabled()) {
            abort(422, 'Two-Factor Authentication is not enabled.');
        }

        if ($user->hasViewedRecoveryCodes()) {
            abort(410, 'Recovery codes already viewed. Regenerate to view again.');
        }

        $recoveryCodes = $user->two_factor_recovery_codes
            ? json_decode(decrypt($user->two_factor_recovery_codes), true) ?? []
            : [];

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

    /**
     * Request regeneration of recovery codes (requires password + 2FA code).
     */
    public function requestRegeneration(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $request->validate([
                'password' => 'required|string',
                'code' => 'required|string|size:6',
            ]);

            $password = $request->input('password');
            $code = $request->input('code');

            // Verify password
            if (!Hash::check($password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid password.',
                    'errors' => ['Invalid password.']
                ], 422);
            }

            // Verify 2FA code
            $secret = $user->two_factor_secret ? decrypt($user->two_factor_secret) : null;
            if (!$secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Two-Factor Authentication is not properly configured.',
                    'errors' => ['Two-Factor Authentication is not properly configured.']
                ], 422);
            }

            $totp = TOTP::create($secret);
            if (!$totp->verify($code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid authentication code.',
                    'errors' => ['Invalid authentication code.']
                ], 422);
            }

            // Generate token for email confirmation
            $token = $user->generateRecoveryCodesRegenerationToken();

            // Send confirmation email
            Mail::to($user->email)->send(new \App\Mail\RecoveryCodesRegenerationConfirmation($user, $token));

            return response()->json([
                'success' => true,
                'message' => 'Regeneration email sent. Check your inbox to confirm.'
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to request regeneration.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Confirm regeneration using emailed token.
     */
    public function confirmRegeneration(Request $request): JsonResponse
    {
        try {
            $token = $request->query('token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Regeneration token is required.',
                    'errors' => ['Regeneration token is required.']
                ], 422);
            }

            $user = $request->user();

            // Validate token
            if (!$user->validateRecoveryCodesRegenerationToken($token)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired regeneration token.',
                    'errors' => ['Invalid or expired regeneration token.']
                ], 422);
            }

            // Generate new recovery codes
            $newRecoveryCodes = collect(range(1, 10))
                ->map(fn () => bin2hex(random_bytes(8)))
                ->all();

            // Update user with new codes
            $user->forceFill([
                'two_factor_recovery_codes' => encrypt(json_encode($newRecoveryCodes)),
                'two_factor_used_recovery_codes' => [],
                'two_factor_recovery_codes_viewed_at' => null,
                'recovery_codes_regeneration_token' => null,
                'recovery_codes_regeneration_expires_at' => null,
            ])->save();

            return response()->json([
                'success' => true,
                'message' => 'Recovery codes regenerated successfully.',
                'recovery_codes' => $newRecoveryCodes
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm regeneration.',
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }
}
