<?php

namespace App\Domains\Security\UserManagement\Services;

use App\Domains\Security\UserManagement\Models\UserEmail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UserEmailService implements UserEmailServiceContract
{
    public function getById(int $id): UserEmail
    {
        return UserEmail::query()
            ->where('id', $id)
            ->firstOrFail();
    }

    public function getByUserEmail(string $email): UserEmail
    {
        return UserEmail::query()
            ->where('email', $email)
            ->firstOrFail();
    }

    public function getPrimaryEmail(int $userId): UserEmail
    {
        return UserEmail::query()
            ->where('user_id', $userId)
            ->where('is_primary', true)
            ->firstOrFail();
    }

    public function getByVerificationCode(string $code): UserEmail
    {
        return UserEmail::query()
            ->where('email_verification_code', $code)
            ->firstOrFail();
    }

    public function markAsVerified(UserEmail $userEmail): bool
    {
        return $userEmail->update([
            'verified_at' => now(),
            'verification_token' => null,
            'email_verification_code' => null,
        ]);
    }

    public function sendEmailVerificationNotification(UserEmail $userEmail): array
    {

        if ($userEmail->hasVerifiedEmail()) {
            return [
                'status' => 'success',
                'message' => 'Email is already verified.',
                'data' => null,
            ];
        }

        $this->sendEmailVerificationNotification($userEmail);

        return [
            'status' => 'success',
            'message' => 'Verification email sent.',
            'data' => null,
        ];
    }

    public function generateVerificationToken(UserEmail $userEmail): string
    {
        $token = Str::random(60);
        $userEmail->update(['verification_token' => $token]);

        return $token;
    }

    public function generateEmailVerificationCode(UserEmail $userEmail): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $userEmail->update(['email_verification_code' => $code]);

        return $code;
    }

    public function verifyWithCode(string $code): bool
    {
        $userEmail = $this->getByVerificationCode($code);

        if ($userEmail->email_verification_code === $code) {
            return $this->markAsVerified($userEmail);
        }

        return false;
    }

    public function makePrimary(string $email): bool
    {
        $userEmail = $this->getByUserEmail($email);

        if (! $userEmail->verified_at) {
            throw ValidationException::withMessages([
                'error' => 'Email is not verified.',
            ]);
        }

        UserEmail::query()
            ->where('user_id', $userEmail->user_id)
            ->where('id', '!=', $userEmail->id)
            ->update(['is_primary' => false]);

        // Set this email as primary
        return $userEmail->update(['is_primary' => true]);
    }

    public function canBeDeleted(string $email): bool
    {
        $userEmail = $this->getByUserEmail($email);

        if ($userEmail->is_primary) {
            $otherVerifiedEmails = UserEmail::query()
                ->where('user_id', $userEmail->user_id)
                ->where('id', '!=', $userEmail->id)
                ->whereNotNull('verified_at')
                ->count();

            return $otherVerifiedEmails > 0;
        }

        return true;
    }
}
