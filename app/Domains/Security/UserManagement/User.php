<?php

namespace App\Domains\Security\UserManagement;

use Illuminate\Support\Facades\Facade;
use App\Domains$1$2;

class User extends Facade
{
    /**
     * @method static getById(int $id): User
     * @method static getByUserEmail(string $email): User
     * @method static getByUsername(string $username): User
     * @method static getRoles(User $user): Collection
     * @method static getAbilities(User $user): Collection
     */

    protected static function getFacadeAccessor(): string
    {
        return UserServiceContract::class;
    }
}
