<?php

namespace App\Domains\Security\Authentication\Inputs;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use App\Domains$1$2;

class AuthenticationRegisterUserEmailInput
{
    public int $user_id;
    public string $email;
    public bool $is_primary;
    public function __construct(array $data)
    {
        $this->user_id = $data['user_id'];
        $this->email = $data['email'];
        $this->is_primary = $data['is_primary'] ?? false;
    }
}
