<?php

namespace App\Domains\Settings\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Domains\Settings\Http\Requests\Settings\TwoFactorAuthenticationRequest;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use OTPHP\TOTP;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class TwoFactorAuthenticationController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword')
            ? [new Middleware('password.confirm', only: ['show'])]
            : [];
    }

    /**
     * Show the user's two-factor authentication settings page.
     */
    public function show(TwoFactorAuthenticationRequest $request): Response
    {
        $request->ensureStateIsValid();
        $user = $request->user();
        $recoveryCodes = [];
        if ($user->hasEnabledTwoFactorAuthentication() && $user->two_factor_recovery_codes) {
            $recoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true) ?? [];
        }
        return Inertia::render('settings/TwoFactor', [
            'twoFactorEnabled' => $user->hasEnabledTwoFactorAuthentication(),
            'requiresConfirmation' => Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm'),
            'recoveryCodes' => $recoveryCodes,
        ]);
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function store(TwoFactorAuthenticationRequest $request)
    {
        $user = $request->user();
        if (! $user->hasEnabledTwoFactorAuthentication()) {
            $user->forceFill([
                'two_factor_secret' => encrypt(bin2hex(random_bytes(32))),
                'two_factor_confirmed_at' => null,
                'two_factor_recovery_codes' => encrypt(json_encode(collect(range(1, 8))->map(fn () => bin2hex(random_bytes(8)))->all())),
            ])->save();
        }
        // Optionally, you can return recovery codes or other setup data here
        return redirect()->route('two-factor.show')->with('success', 'Two-factor authentication enabled.');
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function destroy(TwoFactorAuthenticationRequest $request)
    {
        $user = $request->user();
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();
        return redirect()->route('two-factor.show')->with('success', 'Two-factor authentication disabled.');
    }

    /**
     * Get QR code and manual entry for 2FA setup.
     */
    public function getSetupData(Request $request)
    {
        $user = $request->user();
        $secret = $user->two_factor_secret ? decrypt($user->two_factor_secret) : bin2hex(random_bytes(32));
        $totp = TOTP::create($secret);
        $totp->setLabel($user->email);
        $totp->setIssuer(config('app.name'));
        $qrCode = new QrCode($totp->getProvisioningUri());
        $writer = new PngWriter();
        $qrCodeData = base64_encode($writer->write($qrCode)->getString());
        return response()->json([
            'qrCode' => $qrCodeData,
            'manualEntry' => $secret,
        ]);
    }

    /**
     * Validate 2FA code and enable 2FA.
     */
    public function confirm(Request $request)
    {
        $user = $request->user();
        $secret = $user->two_factor_secret ? decrypt($user->two_factor_secret) : null;
        if (! $secret) {
            return response()->json(['error' => 'No secret found'], 400);
        }
        $totp = TOTP::create($secret);
        if (! $totp->verify($request->input('code'))) {
            return response()->json(['error' => 'Invalid code'], 422);
        }
        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => encrypt(json_encode(collect(range(1, 10))->map(fn () => bin2hex(random_bytes(8)))->all())),
        ])->save();
        return response()->json(['success' => true]);
    }

    /**
     * Download recovery codes (only once).
     */
    public function downloadRecoveryCodes(Request $request)
    {
        $user = $request->user();
        $codes = $user->two_factor_recovery_codes ? json_decode(decrypt($user->two_factor_recovery_codes), true) : [];
        // Mark codes as downloaded (could add a flag in user model)
        // For now, just return as txt
        return response(implode("\n", $codes), 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="recovery-codes.txt"',
        ]);
    }

    /**
     * Mark a recovery code as used.
     */
    public function useRecoveryCode(Request $request)
    {
        $user = $request->user();
        $code = $request->input('code');
        $codes = $user->two_factor_recovery_codes ? json_decode(decrypt($user->two_factor_recovery_codes), true) : [];
        if (!in_array($code, $codes)) {
            return response()->json(['error' => 'Invalid code'], 422);
        }
        $codes = array_values(array_diff($codes, [$code]));
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();
        $warning = count($codes) < 2;
        return response()->json(['success' => true, 'warning' => $warning]);
    }

    /**
     * Regenerate recovery codes (requires password + 2FA validation, sends email).
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();
        $password = $request->input('password');
        $code = $request->input('code');
        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid password'], 422);
        }
        $secret = $user->two_factor_secret ? decrypt($user->two_factor_secret) : null;
        $totp = TOTP::create($secret);
        if (! $totp->verify($code)) {
            return response()->json(['error' => 'Invalid 2FA code'], 422);
        }
        // Send confirmation email
        Mail::to($user->email)->send(new \App\Mail\RecoveryCodesRegenerationConfirmation());
        // Set a flag (e.g., user->pending_recovery_codes_regeneration = true)
        $user->forceFill(['pending_recovery_codes_regeneration' => true])->save();
        return response()->json(['success' => true]);
    }

    /**
     * Confirm regeneration (from email link), show new codes (only once).
     */
    public function confirmRegeneration(Request $request)
    {
        $user = $request->user();
        if (! $user->pending_recovery_codes_regeneration) {
            return response()->json(['error' => 'No pending regeneration'], 400);
        }
        $codes = collect(range(1, 10))->map(fn () => bin2hex(random_bytes(8)))->all();
        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
            'pending_recovery_codes_regeneration' => false,
        ])->save();
        return response()->json(['codes' => $codes]);
    }
}
