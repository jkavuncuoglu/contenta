<?php

namespace App\Domains\Security\UserManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Security\UserManagement\Models\User;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function index()
    {

        return Inertia::render('admin/userManagement/Index', [
            'users' => User::all()
        ]);
    }
}
