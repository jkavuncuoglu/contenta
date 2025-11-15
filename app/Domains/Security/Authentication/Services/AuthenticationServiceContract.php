<?php

namespace App\Domains\Security\Authentication\Services;

use App\Domains\Security\Authentication\Inputs\AuthenticationChangePasswordInput;
use App\Domains\Security\Authentication\Inputs\AuthenticationLoginInput;
use App\Domains\Security\Authentication\Inputs\AuthenticationRegisterUserInput;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface AuthenticationServiceContract
{
    public function register(AuthenticationRegisterUserInput $input): JsonResponse;

    public function login(AuthenticationLoginInput $input): JsonResponse;

    public function logout(Request $request): JsonResponse;

    public function forgotPassword(string $email): JsonResponse;

    public function changePassword(AuthenticationChangePasswordInput $input): JsonResponse;

    public function resetPassword(AuthenticationChangePasswordInput $input): JsonResponse;
}
