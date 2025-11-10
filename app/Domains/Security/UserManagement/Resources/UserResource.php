<?php

namespace App\Domains\Security\UserManagement\Resources;

use App\Domains\Security\UserManagement\Models\User;
use App\Domains\Security\UserManagement\Models\UserEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var User $user */
        $user = $this->resource;

        $primaryEmail = $this->getPrimaryEmail();

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'primary_email' => $primaryEmail?->email,
            'primary_email_verified_at' => $primaryEmail?->verified_at,
            'bio' => $user->bio,
            'avatar' => $user->avatar,
            'timezone' => $user->timezone,
            'language' => $user->language,
            'preferences' => $user->preferences,
            'social_links' => $user->social_links,
            'roles' => $user->getRoleNames()->toArray(),
            'permissions' => $user->getAllPermissions()->pluck('name')->toArray(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    private function getPrimaryEmail(): ?UserEmail
    {
        /** @var User $user */
        $user = $this->resource;
        return $user->emails()->where('is_primary', true)->first();
    }
}
