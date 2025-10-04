<?php

namespace App\Domains\Security\Authentication\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Domains$1$2;
use App\Domains$1$2;
use App\Domains$1$2;

interface AuthenticationServiceContract
{
    public function register(AuthenticationRegisterUserInput $input): JsonResponse;
    public function login(AuthenticationLoginInput $input): JsonResponse;

    public function logout(Request $request): JsonResponse;

    public function forgotPassword(string $email): JsonResponse;

    public function changePassword(AuthenticationChangePasswordInput $input): JsonResponse;
}
