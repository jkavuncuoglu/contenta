<?php

namespace App\Domains\Security\Authentication\Http\Controllers;

use App\Domains\Security\Authentication\Authentication;
use App\Domains\Security\Authentication\Http\Requests\ChangePasswordRequest;
use App\Domains\Security\Authentication\Http\Requests\ForgotPasswordRequest;
use App\Domains\Security\Authentication\Http\Requests\UserLoginRequest;
use App\Domains\Security\Authentication\Http\Requests\UserRegistrationRequest;
use App\Domains\Security\Authentication\Inputs\AuthenticationChangePasswordInput;
use App\Domains\Security\Authentication\Inputs\AuthenticationLoginInput;
use App\Domains\Security\Authentication\Inputs\UserUpdateInput;
use App\Domains\Security\UserManagement\Models\UserEmail;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Nette\Schema\ValidationException;

class AuthenticationController extends Controller
{
    public function register(UserRegistrationRequest $request): JsonResponse
    {
        $input = new UserUpdateInput($request->validated());
        return Authentication::register($input);
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        $input = new AuthenticationLoginInput($request->validated());
        return Authentication::login($input);
    }

    public function logout(Request $request): JsonResponse
    {
        return Authentication::logout($request);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $input = new AuthenticationLoginInput($request->email);

        return Authentication::forgotPassword($input);
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $input = new AuthenticationChangePasswordInput($request->validated());
        return Authentication::changePassword($input);
    }

    public function resetPassword(ChangePasswordRequest $request): JsonResponse
    {
        $input = new AuthenticationChangePasswordInput($request->validated());
        return Authentication::resetPassword($input);
    }

    public function sendEmailVerificationNotification(Request $request): JsonResponse
    {
        $userEmail = UserEmail::getByUserEmail($request->userEmail);
        $response = $userEmail->sendEmailVerificationNotification();
        return response()->json($response);
    }

    public function verifyEmail(Request $request)
    {
        $userEmail = UserEmail::getByUserEmail($request->userEmail);
        $response = $userEmail->verifyEmail($request->hash);
        return response()->json($response);
    }


}
