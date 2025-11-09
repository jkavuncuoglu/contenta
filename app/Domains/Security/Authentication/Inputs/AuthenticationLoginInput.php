<?php

namespace App\Domains\Security\Authentication\Inputs;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthenticationLoginInput
{
    public string $ip;
    public string $email;
    public string $password;

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(array $data)
    {
        $this->ip = $data['ip'];
        $this->email = $data['email'];
        $this->password = $data['password'];
    }
}
