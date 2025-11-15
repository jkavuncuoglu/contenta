<?php

namespace App\Models;

use App\Domains\Security\UserManagement\Models\User as DomainUser;

/**
 * App\Models\User is an alias/extension of the domain User model.
 * This exists for backward compatibility and to maintain the App\Models namespace.
 * All functionality is inherited from the domain model.
 */
class User extends DomainUser
{
    // All functionality is inherited from App\Domains\Security\UserManagement\Models\User
}
