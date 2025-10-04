<?php

namespace App\Domains\Security\UserManagement\Services;

use \Illuminate\Support\Collection;
use App\Domains$1$2;

interface UserServiceContract
{
    public function getById(int $id): User;
    public function getByUserEmail(string $email): User;

    public function getByUsername(string $username): User;

    public function getRoles(User $user): Collection;

    public function getAbilities(User $user): Collection;
}
