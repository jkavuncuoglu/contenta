<?php

namespace App\Domains\Security\UserManagement\Services;


use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use App\Domains\Security\UserManagement\Inputs\UserUpdateInput;
use App\Domains\Security\UserManagement\Models\User;
use App\Domains\Security\UserManagement\Models\UserEmail;
use App\Domains\Security\UserManagement\Resources\UserResource;
use App\Domains\Security\UserManagement\Services\UserServiceContract;

class UserService implements UserServiceContract
{
    public function getById(int $id): User
    {
        return User::query()
            ->findOrFail($id);
    }

    public function getByUserEmail(string $email): User
    {
        $userEmail = UserEmail::query()
            ->where('email', $email)
            ->with('user')
            ->first();

        return $userEmail->user;
    }

    public function getByUsername(string $username): User
    {
        return User::query()
            ->where('username', $username)
            ->firstOrFail();
    }

    public function getRoles(User $user): Collection
    {
        return $user->getRoleNames();
    }

    public function getAbilities(User $user): Collection
    {
        return $user->getAllPermissions()->pluck('name');
    }

    public function update(User $user, UserUpdateInput $input): JsonResponse
    {
        $user->update($input->toArray());

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated',
            'data' => ['user' => UserResource::make($user)],
        ]);
    }
}
