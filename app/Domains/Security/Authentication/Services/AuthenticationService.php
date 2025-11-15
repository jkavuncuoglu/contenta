<?php

namespace App\Domains\Security\Authentication\Services;

use App\Domains\Security\Authentication\Inputs\AuthenticationChangePasswordInput;
use App\Domains\Security\Authentication\Inputs\AuthenticationLoginInput;
use App\Domains\Security\Authentication\Inputs\AuthenticationRegisterUserEmailInput;
use App\Domains\Security\Authentication\Inputs\AuthenticationRegisterUserInput;
use App\Domains\Security\Authentication\Mail\ResetPasswordEmail;
use App\Domains\Security\UserManagement\Models\User;
use App\Domains\Security\UserManagement\Models\UserEmail;
use App\Domains\Security\UserManagement\Resources\UserResource;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthenticationService implements AuthenticationServiceContract
{
    public function register(AuthenticationRegisterUserInput $input): JsonResponse
    {
        $user = User::create($input->toArray());

        $userEmailInput = new AuthenticationRegisterUserEmailInput([
            'user_id' => $user->id,
            'email' => $input->email,
            'is_primary' => true,
        ]);

        $userEmail = UserEmail::create($userEmailInput->toArray());

        $verificationCode = $this->generateEmailVerificationCode($userEmail);

        event(new Registered($user));

        return response()->json([
            'status' => 'success',
            'message' => 'Registration successful. Please verify your email.',
            'data' => [
                'user' => UserResource::make($user),
            ],
        ], 201);
    }

    public function login(AuthenticationLoginInput $input): JsonResponse
    {
        $userEmail = UserEmail::where('email', $input->email)->firstOrFail();
        $user = $userEmail->user;

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'error' => 'Invalid credentials.',
            ]);
        }

        if (! Hash::check($input->password, $user->password)) {
            throw ValidationException::withMessages([
                'error' => 'Invalid credentials.',
            ]);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $input->ip,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'data' => [
                'user' => UserResource::make($user),
            ],
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function changePassword(AuthenticationChangePasswordInput $input): JsonResponse
    {
        $userEmail = UserEmail::where('email', $input->email)->firstOrFail();
        $user = $userEmail->user;

        if (! Hash::check($input->currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'error' => 'Invalid current password.',
            ]);
        }

        $user->password = Hash::make($input->newPassword);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Password updated successfully.',
            'data' => null,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request
            ->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User logged out successfully',
            'data' => null,
        ]);
    }

    public function forgotPassword(string $email): JsonResponse
    {
        $user = $this->resolveActiveUserByEmailOrFail($email);

        $token = Password::broker()->createToken($user);

        $fronendBase = config('app.frontend_base') ?: config('app.url');

        if (empty($fronendBase)) {
            throw ValidationException::withMessages([
                'error' => 'No frontend base URL configured.',
            ]);
        }

        $resetUrl = rtrim((string) $fronendBase, '/').'/reset-password'
            .'?token='.$token
            .'&email='.urlencode($user->email);

        if (Mail::to($email)->send(
            new ResetPasswordEmail(
                $user->id,
                $email,
                $resetUrl,
                $token
            )
        )) {
            return response()->json([
                'status' => 'success',
                'message' => 'Password reset link sent to your email.',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send password reset link.',
        ]);
    }

    public function resetPassword(AuthenticationChangePasswordInput $input): JsonResponse
    {
        $user = $this->resolveActiveUserByEmailOrFail($input->email);

        $user->forceFill([
            'password' => Hash::make($input->newPassword),
            'remember_token' => null,
        ])->save();

        event(new PasswordReset($user));

        return response()->json([
            'status' => 'success',
            'message' => 'Password has been reset.',
            'data' => null,
        ]);
    }

    private function resolveActiveUserByEmailOrFail(string $email): User
    {
        $userEmail = UserEmail::where('email', $email)->first();

        if (empty($userEmail)) {
            throw ValidationException::withMessages([
                'error' => 'No user found with that email address.',
            ]);
        }

        $user = $userEmail->user;

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'error' => 'Invalid credentials.',
            ]);
        }

        return $user;
    }

    private function generateEmailVerificationCode(UserEmail $userEmail): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $userEmail->update(['email_verification_code' => $code]);

        return $code;
    }
}
