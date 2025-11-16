<?php

namespace App\Domains\Security\UserManagement\Inputs;

class UserUpdateInput
{
    public ?string $name;

    public ?string $email;

    public ?string $first_name;

    public ?string $last_name;

    public ?string $username;

    public ?string $bio;

    public ?string $avatar;

    public ?string $timezone;

    public ?string $language;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $preferences;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $social_links;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? null;
        $this->email = $data['email'] ?? null;
        $this->first_name = $data['first_name'] ?? null;
        $this->last_name = $data['last_name'] ?? null;
        $this->username = $data['username'] ?? null;
        $this->bio = $data['bio'] ?? null;
        $this->avatar = $data['avatar'] ?? null;
        $this->timezone = $data['timezone'] ?? null;
        $this->language = $data['language'] ?? null;
        $this->preferences = isset($data['preferences']) && is_array($data['preferences']) ? $data['preferences'] : null;
        $this->social_links = isset($data['social_links']) && is_array($data['social_links']) ? $data['social_links'] : null;
    }

    /**
     * Return only set fields (for safe mass assignment).
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'timezone' => $this->timezone,
            'language' => $this->language,
            'preferences' => $this->preferences,
            'social_links' => $this->social_links,
        ], static fn ($v) => $v !== null);
    }
}
