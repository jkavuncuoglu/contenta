<?php

namespace App\Domains\Security\UserManagement\Services;

use App\Domains$1$2;
use App\Domains$1$2;

interface UserEmailServiceContract
{
    public function getByUserEmail(string $email): User;
    public function getById(int $id): User;
    public function getPrimaryEmail(int $userId): UserEmail;
    public function getByVerificationCode(string $code): UserEmail;
    public function markAsVerified(UserEmail $userEmail): bool;

    public function sendEmailVerificationNotification(UserEmail $userEmail): array;
    public function generateVerificationToken(string $code): bool;
    public function generateEmailVerificationCode(UserEmail $userEmail): string;
    public function verifyWithCode(string $code): bool;
    public function makePrimary(string $email): bool;
    public function canBeDeleted(string $email): bool;

}
