<?php

namespace App\Domains\Security\Authentication\Inputs;

class AuthenticationChangePasswordInput
{
    public string $email;

    public string $currentPassword;

    public string $newPassword;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->email = $data['email'];
        $this->currentPassword = $data['current_password'];
        $this->newPassword = $data['newPassword'];
    }
}
