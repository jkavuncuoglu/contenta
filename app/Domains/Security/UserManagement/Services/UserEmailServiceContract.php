<?php

namespace App\Domains\Security\UserManagement\Services;

use App\Domains\Security\UserManagement\Models\UserEmail;

interface UserEmailServiceContract
{
    public function getByUserEmail(string $email): UserEmail;

    public function getById(int $id): UserEmail;

    public function getPrimaryEmail(int $userId): UserEmail;

    public function getByVerificationCode(string $code): UserEmail;

    public function markAsVerified(UserEmail $userEmail): bool;

    /**
     * @return array<string, mixed>
     */
    public function sendEmailVerificationNotification(UserEmail $userEmail): array;

    public function generateVerificationToken(UserEmail $userEmail): string;

    public function generateEmailVerificationCode(UserEmail $userEmail): string;

    public function verifyWithCode(string $code): bool;

    public function makePrimary(string $email): bool;

    public function canBeDeleted(string $email): bool;
}
