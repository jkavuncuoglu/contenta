<?php

namespace App\Domains\Security\UserManagement\Services;

use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Support\Collection;

interface UserServiceContract
{
    public function getById(int $id): User;
    public function getByUserEmail(string $email): User;

    public function getByUsername(string $username): User;

    public function getRoles(User $user): Collection;

    public function getAbilities(User $user): Collection;
}
