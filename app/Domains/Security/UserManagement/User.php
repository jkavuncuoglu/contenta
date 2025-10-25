<?php

namespace App\Domains\Security\UserManagement;

use App\Domains\Security\UserManagement\Services\UserServiceContract;
use Illuminate\Support\Facades\Facade;

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
