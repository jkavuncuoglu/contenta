<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use App\Domains\Security\Authentication\Http\Requests\UserRegistrationRequest;

class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return Inertia::render('auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(UserRegistrationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return to_route('dashboard');
    }

    /**
     * Check if a username is available.
     */
    public function checkUsername(Request $request)
    {
        $username = $request->query('username');
        $available = !User::where('username', $username)->exists();
        return Inertia::render('auth/Register', ['available' => $available]);
    }

    /**
     * Check if an email is available (unique in users and user_emails tables).
     */
    public function checkEmail(Request $request)
    {
        $email = $request->query('email');
        $existsInUsers = \App\Models\User::where('email', $email)->exists();
        $existsInUserEmails = false;
        if (\Schema::hasTable('user_emails')) {
            $existsInUserEmails = \DB::table('user_emails')->where('email', $email)->exists();
        }
        $available = !$existsInUsers && !$existsInUserEmails;
        return Inertia::render('auth/Register', ['emailAvailable' => $available]);
    }
}
