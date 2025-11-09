<?php

namespace App\Domains\Security\UserManagement;

use App\Domains\Security\UserManagement\Models\User as UserModel;
use App\Domains\Security\UserManagement\Services\UserServiceContract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static UserModel getById(int $id)
 * @method static UserModel getByUserEmail(string $email)
 * @method static UserModel getByUsername(string $username)
 * @method static Collection getRoles(UserModel $user)
 * @method static Collection getAbilities(UserModel $user)
 */
class User extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return UserServiceContract::class;
    }
}
