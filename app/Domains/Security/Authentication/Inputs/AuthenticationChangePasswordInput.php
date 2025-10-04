<?php

namespace App\Domains\Security\Authentication\Inputs;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Domains$1$2;
use App\Domains$1$2;
use App\Domains$1$2;
use App\Domains$1$2;

class AuthenticationChangePasswordInput
{
    public string $email;
    public string $currentPassword;
    public string $newPassword;
    public function __construct(array $data)
    {
        $this->email = $data['email'];
        $this->currentPassword = $data['current_password'];
        $this->newPassword = $data['newPassword'];
    }
}
