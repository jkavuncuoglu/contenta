<?php

namespace App\Domains\Security\UserManagement\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Domains$1$2;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        $primaryEmail = $this->getPrimaryEmail();

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'primary_email' => $primaryEmail->email,
            'primary_email_verified_at' => $primaryEmail->verified_at,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'preferences' => $this->preferences,
            'social_links' => $this->social_links,
            'roles' => $this->getRoleNames()->toArray() || [],
            'permissions' => $this->getAllPermissions()->pluck('name')->toArray() || [],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    private function getPrimaryEmail(): UserEmail
    {
        return $this->emails()->where('is_primary', true)->first();
    }
}
