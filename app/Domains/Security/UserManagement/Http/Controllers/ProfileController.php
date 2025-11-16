<?php

namespace App\Domains\Security\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('settings/Profile', [
            'user' => Auth::user(),
        ]);
    }
}
