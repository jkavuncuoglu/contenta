<?php

namespace App\Domains\Security\UserManagement;

use App\Domains\Security\UserManagement\Models\UserEmail as UserEmailModel;
use App\Domains\Security\UserManagement\Services\UserEmailServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static UserEmailModel getByUserEmail(string $email)
 * @method static UserEmailModel getById(int $id)
 * @method static UserEmailModel getPrimaryEmail(int $userId)
 * @method static UserEmailModel getByVerificationCode(string $code)
 * @method static bool markAsVerified(UserEmailModel $userEmail)
 * @method static array<string, mixed> sendEmailVerificationNotification(UserEmailModel $userEmail)
 * @method static string generateVerificationToken(UserEmailModel $userEmail)
 * @method static string generateEmailVerificationCode(UserEmailModel $userEmail)
 * @method static bool verifyWithCode(string $code)
 * @method static bool makePrimary(string $email)
 * @method static bool canBeDeleted(string $email)
 */
class UserEmail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserEmailServiceContract::class;
    }
}
