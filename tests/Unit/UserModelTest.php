<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laragear\WebAuthn\Contracts\WebAuthnAuthenticatable;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_extends_authenticatable(): void
    {
        // Assert
        $this->assertTrue(is_subclass_of(User::class, Authenticatable::class));
    }

    public function test_user_implements_webauthn_authenticatable(): void
    {
        // Assert
        $this->assertTrue(is_a(User::class, WebAuthnAuthenticatable::class, true));
    }

    public function test_user_can_be_instantiated(): void
    {
        // Arrange & Act
        $user = new User();

        // Assert
        $this->assertInstanceOf(User::class, $user);
        $this->assertInstanceOf(Authenticatable::class, $user);
    }

    public function test_user_class_exists(): void
    {
        // Assert
        $this->assertTrue(class_exists(User::class));
    }

    public function test_user_has_factory_trait(): void
    {
        // Assert
        $this->assertContains('Illuminate\Database\Eloquent\Factories\HasFactory', class_uses(User::class));
    }

    public function test_user_fillable_attributes_include_essential_fields(): void
    {
        // Arrange
        $user = new User();

        // Assert
        $this->assertContains('first_name', $user->getFillable());
        $this->assertContains('last_name', $user->getFillable());
        $this->assertContains('email', $user->getFillable());
        $this->assertContains('password', $user->getFillable());
    }

    public function test_user_hidden_attributes_include_sensitive_data(): void
    {
        // Arrange
        $user = new User();

        // Assert
        $this->assertContains('password', $user->getHidden());
        $this->assertContains('remember_token', $user->getHidden());
        $this->assertContains('two_factor_secret', $user->getHidden());
    }
}
