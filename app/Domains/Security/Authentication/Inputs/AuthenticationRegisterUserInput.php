<?php

namespace App\Domains\Security\Authentication\Inputs;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Domains$1$2;

class AuthenticationRegisterUserInput
{
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $username;
    public ?string $bio;
    public ?string $avatar;
    public ?string $timezone;
    public ?string $language;
    public string $is_active;
    public ?Carbon $email_verified_at;
    public string $password;

    public function __construct(array $data)
    {
        $this->first_name = $data['first_name'];
        $this->last_name = $data['last_name'];
        $this->email = $data['email'];
        $this->username = $data['username'];
        $this->bio = $data['bio'] ?? null;
        $this->avatar = $data['avatar'] ?? UserDefaults::DEFAULT_AVATAR;
        $this->timezone = $data['timezone'] ?? UserDefaults::DEFAULT_TIMEZONE;
        $this->language = $data['language'] ?? UserDefaults::DEFAULT_LANGUAGE;;
        $this->is_active = $data['is_active'] ?? 1;
        $this->email_verified_at = null;
        $this->password = Hash::make($data['password']);
    }
}
