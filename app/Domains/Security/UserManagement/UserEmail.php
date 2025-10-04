<?php

namespace App\Domains\Security\UserManagement;

use Illuminate\Support\Facades\Facade;
use App\Domains$1$2;

class UserEmail extends Facade
{
    /**
     * @method static getByUserEmail(string $email): UserEmail
     * @method static getById(int $id): UserEmail
     * @method static getPrimaryEmail(int $userId): UserEmail
     * @method static getByVerificationCode(string $code): UserEmail
     * @method static markAsVerified(UserEmail $userEmail): bool
     * @method static sendEmailVerificationNotification(UserEmail $userEmail): array
     * @method static generateVerificationToken(string $code): bool
     * @method static generateEmailVerificationCode(UserEmail $userEmail): string
     * @method static verifyWithCode(string $code): bool
     * @method static makePrimary(string $email): bool
     * @method static canBeDeleted(string $email): bool
     */

    protected static function getFacadeAccessor(): string
    {
        return UserEmailServiceContract::class;
    }
}
