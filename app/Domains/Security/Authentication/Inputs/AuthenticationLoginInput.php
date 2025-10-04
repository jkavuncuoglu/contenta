<?php

namespace App\Domains\Security\Authentication\Inputs;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Domains$1$2;
use App\Domains$1$2;

class AuthenticationLoginInput
{
    public string $ip;
    public string $email;
    public string $password;

    public function __construct(array $data)
    {
        $this->ip = $data['ip'];
        $this->email = $data['email'];
        $this->password = $data['password'];
    }
}
