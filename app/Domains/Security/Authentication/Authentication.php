<?php

namespace App\Domains\Security\Authentication;

use App\Domains\Security\Authentication\Inputs\AuthenticationChangePasswordInput;
use App\Domains\Security\Authentication\Inputs\AuthenticationLoginInput;
use App\Domains\Security\Authentication\Inputs\AuthenticationRegisterUserInput;
use App\Domains\Security\Authentication\Services\AuthenticationServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @method static JsonResponse register(AuthenticationRegisterUserInput $input)
 * @method static JsonResponse login(AuthenticationLoginInput $input)
 * @method static JsonResponse logout(Request $request)
 * @method static JsonResponse forgotPassword(string $email)
 * @method static JsonResponse changePassword(AuthenticationChangePasswordInput $input)
 * @method static JsonResponse resetPassword(AuthenticationChangePasswordInput $input)
 */
class Authentication extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuthenticationServiceContract::class;
    }
}
