<?php

declare(strict_types=1);

namespace App\Domains\Security\Tests\UnitTests;

use App\Domains\Security\UserManagement\Inputs\UserUpdateInput;
use App\Domains\Security\UserManagement\Models\User;
use App\Domains\Security\UserManagement\Models\UserEmail;
use App\Domains\Security\UserManagement\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService;
    }

    public function test_get_by_id_returns_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $foundUser = $this->service->getById($user->id);

        // Assert
        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_get_by_id_throws_exception_for_nonexistent_user(): void
    {
        // Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act
        $this->service->getById(999999);
    }

    public function test_get_by_user_email_returns_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        $userEmail = UserEmail::create([
            'user_id' => $user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
            'verified_at' => now(),
        ]);

        // Act
        $foundUser = $this->service->getByUserEmail('test@example.com');

        // Assert
        $this->assertEquals($user->id, $foundUser->id);
    }

    public function test_get_by_username_returns_user(): void
    {
        // Arrange
        $user = User::factory()->create(['username' => 'testuser']);

        // Act
        $foundUser = $this->service->getByUsername('testuser');

        // Assert
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('testuser', $foundUser->username);
    }

    public function test_get_by_username_throws_exception_for_nonexistent_user(): void
    {
        // Assert
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        // Act
        $this->service->getByUsername('nonexistent');
    }

    public function test_get_roles_returns_user_roles(): void
    {
        // Arrange
        $user = User::factory()->create();
        $role1 = Role::create(['name' => 'admin']);
        $role2 = Role::create(['name' => 'editor']);
        $user->assignRole($role1, $role2);

        // Act
        $roles = $this->service->getRoles($user);

        // Assert
        $this->assertCount(2, $roles);
        $this->assertTrue($roles->contains('admin'));
        $this->assertTrue($roles->contains('editor'));
    }

    public function test_get_abilities_returns_user_permissions(): void
    {
        // Arrange
        $user = User::factory()->create();
        $permission1 = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $permission2 = Permission::create(['name' => 'delete posts', 'guard_name' => 'web']);
        $user->givePermissionTo($permission1, $permission2);

        // Act
        $abilities = $this->service->getAbilities($user);

        // Assert
        $this->assertCount(2, $abilities);
        $this->assertTrue($abilities->contains('edit posts'));
        $this->assertTrue($abilities->contains('delete posts'));
    }

    public function test_update_updates_user_data(): void
    {
        // Arrange
        $user = User::factory()->create([
            'first_name' => 'Old',
            'last_name' => 'Name',
        ]);

        $input = new UserUpdateInput([
            'first_name' => 'New',
            'last_name' => 'Name',
        ]);

        // Act
        $response = $this->service->update($user, $input);

        // Assert
        $this->assertEquals(200, $response->getStatusCode());
        $user->refresh();
        $this->assertEquals('New', $user->first_name);
    }

    public function test_update_returns_json_response(): void
    {
        // Arrange
        $user = User::factory()->create();
        $input = new UserUpdateInput([
            'first_name' => 'Updated',
            'last_name' => 'User',
        ]);

        // Act
        $response = $this->service->update($user, $input);

        // Assert
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('success', $data['status']);
        $this->assertEquals('Profile updated', $data['message']);
        $this->assertArrayHasKey('user', $data['data']);
    }
}
