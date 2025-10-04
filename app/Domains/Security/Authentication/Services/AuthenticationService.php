<?php

namespace App\Domains\Security\Authentication\Services;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use ResetPasswordEmail;
use App\Domains$1$2;
use App\Domains$1$2;
use App\Domains$1$2;
use App\Domains$1$2;
use App\Domains$1$2;
use App\Domains$1$2;
use App\Domains$1$2;

class AuthenticationService implements AuthenticationServiceContract
{
    public function register(AuthenticationRegisterUserInput $input): JsonResponse
    {
        $user = User::create($input);

        $userEmailInput = new AuthenticationRegisterUserEmailInput([
            'user_id' => $user->id,
            'email' => $input->email,
            'is_primary' => true,
        ]);

        $userEmail = UserEmail::create($userEmailInput);

        $verificationCode = $userEmail->generateEmailVerificationCode();

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
        $user = User::getByUserEmail($input->email);

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'error' => 'Invalid credentials.',
            ]);
        }

        if (!Hash::check($input->password, $user->password)) {
            throw ValidationException::withMessages([
                'error' => 'Invalid credentials.'
            ]);
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $input->id,
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
        $user = User::getByUserEmail($input->email);

        if (!Hash::check($input->currentPassword, $user->password)) {
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

        $resetUrl = rtrim((string) $fronendBase, '/') . '/reset-password'
            . '?token=' . $token
            . '&email=' . urlencode($user->email);

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
            'password' => Hash::make($input->password),
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
        $user = User::getByUserEmail($email);

        if (empty($user)) {
            throw ValidationException::withMessages([
                'error' => 'No user found with that email address.',
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'error' => 'Invalid credentials.',
            ]);
        }

        if (empty($user->email)) {
            $userPrimaryEmail = $user->emails()->where('is_primary', true)->first();

            if (empty($userPrimaryEmail)) {
                $userPrimaryEmail = $user->emails()->first();

                if (empty($userPrimaryEmail)) {

                    throw ValidationException::withMessages([
                        'error' => 'No valid email address found for this user.',
                    ]);
                }
            }

            $user->email = $userPrimaryEmail->email;
            $user->save();
        }

        return $user;
    }

}
