<?php

namespace Tests\Unit;

use App\Domains\Security\UserManagement\Models\User as DomainUser;
use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_user_extends_domain_user(): void
    {
        // Assert
        $this->assertTrue(is_subclass_of(User::class, DomainUser::class));
    }

    public function test_user_can_be_instantiated(): void
    {
        // Arrange & Act
        $user = new User;

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(DomainUser::class, $user);
    }

    public function test_user_class_exists(): void
    {
        // Assert
        $this->assertTrue(class_exists(User::class));
    }
}
