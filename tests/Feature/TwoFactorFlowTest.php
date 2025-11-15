<?php

use App\Domains\Security\UserManagement\Models\User;
use App\Mail\RecoveryCodesRegenerationConfirmation;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use PragmaRX\Google2FA\Google2FA;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('enables two-factor authentication and returns recovery codes only once', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    // Step 1: get setup data
    $setupResp = getJson('/two-factor/setup')->assertOk()->json();
    expect($setupResp)->toHaveKeys(['qrCode', 'manualEntry']);

    // Use the stored encrypted secret to generate valid TOTP
    $secret = decrypt($user->fresh()->two_factor_secret);
    $g2fa = new Google2FA;
    $validCode = $g2fa->getCurrentOtp($secret);

    // Step 2: enable with valid code
    $enableResp = postJson('/two-factor/enable', ['code' => $validCode])
        ->assertOk()
        ->json();

    expect($enableResp['success'])->toBeTrue();
    expect($enableResp['recovery_codes'])->toBeArray()->toHaveCount(10);

    $user = $user->fresh();
    expect($user->two_factor_confirmed_at)->not()->toBeNull();

    // First fetch of recovery codes (should be returned and then marked viewed)
    $codesFirst = getJson('/two-factor/recovery-codes')->assertOk()->json();
    expect($codesFirst['recovery_codes'])->toBeArray();

    // Second fetch should return empty array because already viewed
    $codesSecond = getJson('/two-factor/recovery-codes')->assertOk()->json();
    expect($codesSecond['recovery_codes'])->toBeArray()->toHaveCount(0);
});

it('regenerates recovery codes after password + 2fa verification and email confirmation', function () {
    Mail::fake();

    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    // Setup and enable 2FA first
    getJson('/two-factor/setup')->assertOk();
    $secret = decrypt($user->fresh()->two_factor_secret);
    $g2fa = new Google2FA;
    $code = $g2fa->getCurrentOtp($secret);
    postJson('/two-factor/enable', ['code' => $code])->assertOk();

    $user = $user->fresh();

    // Request regeneration
    $code2 = $g2fa->getCurrentOtp($secret); // new code (still valid window)
    postJson('/two-factor/recovery-codes/regenerate', [
        'password' => 'Password123!',
        'code' => $code2,
    ])->assertOk();

    // Assert mail sent and grab token
    Mail::assertSent(RecoveryCodesRegenerationConfirmation::class, 1);

    $user = $user->fresh();
    expect($user->recovery_codes_regeneration_token)->not()->toBeNull();

    $token = $user->recovery_codes_regeneration_token;

    // Confirm regeneration
    $confirmResp = getJson('/two-factor/recovery-codes/confirm?token='.$token)
        ->assertOk()
        ->json();

    expect($confirmResp['success'])->toBeTrue();
    expect($confirmResp['recovery_codes'])->toBeArray()->toHaveCount(10);

    $user = $user->fresh();
    expect($user->recovery_codes_regeneration_token)->toBeNull();
});

it('disables two-factor authentication successfully', function () {
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);
    actingAs($user);

    // Setup + enable 2FA
    getJson('/two-factor/setup')->assertOk();
    $secret = decrypt($user->fresh()->two_factor_secret);
    $g2fa = new Google2FA;
    $code = $g2fa->getCurrentOtp($secret);
    postJson('/two-factor/enable', ['code' => $code])->assertOk();

    deleteJson('/two-factor/disable')->assertOk();

    $user = $user->fresh();
    expect($user->two_factor_secret)->toBeNull();
    expect($user->two_factor_confirmed_at)->toBeNull();
});
