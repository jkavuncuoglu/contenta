<?php

namespace App\Domains\Security\Authentication\Inputs;

class AuthenticationRegisterUserEmailInput
{
    public int $user_id;

    public string $email;

    public bool $is_primary;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(array $data)
    {
        $this->user_id = $data['user_id'];
        $this->email = $data['email'];
        $this->is_primary = $data['is_primary'] ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'email' => $this->email,
            'is_primary' => $this->is_primary,
        ];
    }
}
