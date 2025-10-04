<?php

namespace App\Domains\Security\Authentication;

use Illuminate\Support\Facades\Facade;
use App\Domains$1$2;
use App\Domains$1$2;

class Authentication extends Facade
{
    /**
     * @method register(AuthenticationRegisterUserInput $input)
     */
    protected static function getFacadeAccessor(): string
    {
        return AuthenticationServiceContract::class;
    }
}
