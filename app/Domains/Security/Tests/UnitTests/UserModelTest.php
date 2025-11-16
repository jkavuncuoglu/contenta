<?php

declare(strict_types=1);

namespace App\Domains\Security\Tests\UnitTests;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\Security\UserManagement\Models\User;
use App\Domains\Security\UserManagement\Models\UserEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laragear\WebAuthn\WebAuthnData;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'username' => 'johndoe',
            'is_active' => true,
        ]);
    }

    public function test_posts_relationship_returns_user_posts(): void
    {
        // Arrange
        $post = Post::factory()->create(['author_id' => $this->user->id]);

        // Act
        $posts = $this->user->posts;

        // Assert
        $this->assertCount(1, $posts);
        $this->assertEquals($post->id, $posts->first()->id);
    }

    public function test_emails_relationship_returns_user_emails(): void
    {
        // Arrange
        $email = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'test@example.com',
            'is_primary' => true,
        ]);

        // Act
        $emails = $this->user->emails;

        // Assert
        $this->assertCount(1, $emails);
        $this->assertEquals($email->id, $emails->first()->id);
    }

    public function test_primary_email_relationship_returns_only_primary_email(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'secondary@example.com',
            'is_primary' => false,
        ]);
        $primaryEmail = UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'primary@example.com',
            'is_primary' => true,
        ]);

        // Act
        $primary = $this->user->primaryEmail;

        // Assert
        $this->assertCount(1, $primary);
        $this->assertEquals($primaryEmail->id, $primary->first()->id);
    }

    public function test_get_full_name_attribute_returns_first_and_last_name(): void
    {
        // Act
        $fullName = $this->user->full_name;

        // Assert
        $this->assertEquals('John Doe', $fullName);
    }

    public function test_get_full_name_attribute_falls_back_to_name(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Jane Smith',
            'first_name' => null,
            'last_name' => null,
        ]);

        // Act
        $fullName = $user->full_name;

        // Assert
        $this->assertEquals('Jane Smith', $fullName);
    }

    public function test_get_full_name_attribute_handles_missing_last_name(): void
    {
        // Arrange
        $user = User::factory()->create([
            'first_name' => 'Alice',
            'last_name' => null,
        ]);

        // Act
        $fullName = $user->full_name;

        // Assert
        $this->assertEquals('Alice', $fullName);
    }

    public function test_is_super_admin_returns_true_for_super_admin(): void
    {
        // Arrange
        $role = Role::create(['name' => 'super-admin']);
        $this->user->assignRole($role);

        // Act & Assert
        $this->assertTrue($this->user->isSuperAdmin());
    }

    public function test_is_super_admin_returns_false_for_non_super_admin(): void
    {
        // Act & Assert
        $this->assertFalse($this->user->isSuperAdmin());
    }

    public function test_is_admin_returns_true_for_super_admin(): void
    {
        // Arrange
        $role = Role::create(['name' => 'super-admin']);
        $this->user->assignRole($role);

        // Act & Assert
        $this->assertTrue($this->user->isAdmin());
    }

    public function test_is_admin_returns_true_for_admin(): void
    {
        // Arrange
        $role = Role::create(['name' => 'admin']);
        $this->user->assignRole($role);

        // Act & Assert
        $this->assertTrue($this->user->isAdmin());
    }

    public function test_is_admin_returns_false_for_non_admin(): void
    {
        // Act & Assert
        $this->assertFalse($this->user->isAdmin());
    }

    public function test_can_edit_posts_returns_true_for_author(): void
    {
        // Arrange
        $role = Role::create(['name' => 'author']);
        $this->user->assignRole($role);

        // Act & Assert
        $this->assertTrue($this->user->canEditPosts());
    }

    public function test_can_edit_posts_returns_true_for_editor(): void
    {
        // Arrange
        $role = Role::create(['name' => 'editor']);
        $this->user->assignRole($role);

        // Act & Assert
        $this->assertTrue($this->user->canEditPosts());
    }

    public function test_can_edit_posts_returns_false_for_non_editor(): void
    {
        // Act & Assert
        $this->assertFalse($this->user->canEditPosts());
    }

    public function test_scope_active_filters_active_users(): void
    {
        // Arrange
        User::factory()->create(['is_active' => false]);
        $activeUser = User::factory()->create(['is_active' => true]);

        // Act
        $activeUsers = User::query()->active()->get();

        // Assert
        $this->assertCount(2, $activeUsers); // Includes setUp user
        $this->assertTrue($activeUsers->contains($activeUser));
    }

    public function test_get_direct_permissions_attribute_returns_array(): void
    {
        // Act
        $permissions = $this->user->direct_permissions;

        // Assert
        $this->assertIsArray($permissions);
    }

    public function test_get_direct_permissions_attribute_returns_empty_array_when_no_permissions(): void
    {
        // Act
        $permissions = $this->user->direct_permissions;

        // Assert
        $this->assertIsArray($permissions);
        $this->assertEmpty($permissions);
    }

    public function test_get_permission_names_attribute_returns_array(): void
    {
        // Act
        $permissions = $this->user->permission_names;

        // Assert
        $this->assertIsArray($permissions);
    }

    public function test_get_permissions_via_roles_attribute_returns_role_permissions(): void
    {
        // Arrange
        $role = Role::create(['name' => 'editor']);
        $permission1 = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $permission2 = Permission::create(['name' => 'publish posts', 'guard_name' => 'web']);
        $role->givePermissionTo($permission1, $permission2);
        $this->user->assignRole($role);

        // Act
        $permissions = $this->user->permissions_via_roles;

        // Assert
        $this->assertIsArray($permissions);
        $this->assertCount(2, $permissions);
        $this->assertContains('edit posts', $permissions);
        $this->assertContains('publish posts', $permissions);
    }

    public function test_get_permissions_via_roles_attribute_returns_empty_array_when_no_roles(): void
    {
        // Act
        $permissions = $this->user->permissions_via_roles;

        // Assert
        $this->assertIsArray($permissions);
        $this->assertEmpty($permissions);
    }

    public function test_get_all_permissions_attribute_returns_array(): void
    {
        // Act
        $permissions = $this->user->all_permissions;

        // Assert
        $this->assertIsArray($permissions);
    }

    public function test_get_can_attribute_returns_array(): void
    {
        // Act
        $can = $this->user->can;

        // Assert
        $this->assertIsArray($can);
    }

    public function test_has_two_factor_enabled_returns_true_when_enabled(): void
    {
        // Arrange
        $this->user->two_factor_secret = encrypt('secret');
        $this->user->two_factor_confirmed_at = now();
        $this->user->save();

        // Act & Assert
        $this->assertTrue($this->user->hasTwoFactorEnabled());
    }

    public function test_has_two_factor_enabled_returns_false_when_not_enabled(): void
    {
        // Act & Assert
        $this->assertFalse($this->user->hasTwoFactorEnabled());
    }

    public function test_get_available_recovery_codes_count(): void
    {
        // Arrange
        $this->user->two_factor_secret = encrypt('secret');
        $this->user->two_factor_confirmed_at = now();
        $this->user->two_factor_recovery_codes = encrypt(json_encode(['code1', 'code2', 'code3', 'code4', 'code5']));
        $this->user->two_factor_used_recovery_codes = ['code1', 'code2'];
        $this->user->save();

        // Act
        $count = $this->user->getAvailableRecoveryCodesCount();

        // Assert
        $this->assertEquals(3, $count);
    }

    public function test_should_show_recovery_codes_warning_returns_true_when_low(): void
    {
        // Arrange
        $this->user->two_factor_secret = encrypt('secret');
        $this->user->two_factor_confirmed_at = now();
        $this->user->two_factor_recovery_codes = encrypt(json_encode(['code1']));
        $this->user->two_factor_used_recovery_codes = [];
        $this->user->save();

        // Act & Assert
        $this->assertTrue($this->user->shouldShowRecoveryCodesWarning());
    }

    public function test_should_show_recovery_codes_warning_returns_false_when_sufficient(): void
    {
        // Arrange
        $this->user->two_factor_secret = encrypt('secret');
        $this->user->two_factor_confirmed_at = now();
        $this->user->two_factor_recovery_codes = encrypt(json_encode(['code1', 'code2', 'code3']));
        $this->user->two_factor_used_recovery_codes = [];
        $this->user->save();

        // Act & Assert
        $this->assertFalse($this->user->shouldShowRecoveryCodesWarning());
    }


    public function test_has_viewed_recovery_codes_returns_true_when_viewed(): void
    {
        // Arrange
        $this->user->forceFill(['two_factor_recovery_codes_viewed_at' => now()])->save();

        // Act & Assert
        $this->assertTrue($this->user->hasViewedRecoveryCodes());
    }

    public function test_has_viewed_recovery_codes_returns_false_when_not_viewed(): void
    {
        // Act & Assert
        $this->assertFalse($this->user->hasViewedRecoveryCodes());
    }

    public function test_mark_recovery_codes_as_viewed(): void
    {
        // Act
        $this->user->forceFill(['two_factor_recovery_codes_viewed_at' => now()])->save();

        // Assert
        $this->user->refresh();
        $this->assertNotNull($this->user->two_factor_recovery_codes_viewed_at);
    }

    public function test_generate_recovery_codes_regeneration_token(): void
    {
        // Arrange
        $token = bin2hex(random_bytes(32));

        // Act
        $this->user->forceFill([
            'recovery_codes_regeneration_token' => $token,
            'recovery_codes_regeneration_expires_at' => now()->addHour(),
        ])->save();

        // Assert
        $this->assertIsString($token);
        $this->assertEquals(64, strlen($token));
        $this->user->refresh();
        $this->assertEquals($token, $this->user->recovery_codes_regeneration_token);
        $this->assertNotNull($this->user->recovery_codes_regeneration_expires_at);
    }

    public function test_validate_recovery_codes_regeneration_token_returns_true_for_valid_token(): void
    {
        // Arrange
        $token = bin2hex(random_bytes(32));
        $this->user->forceFill([
            'recovery_codes_regeneration_token' => $token,
            'recovery_codes_regeneration_expires_at' => now()->addHour(),
        ])->save();

        // Act
        $result = $this->user->validateRecoveryCodesRegenerationToken($token);

        // Assert
        $this->assertTrue($result);
    }

    public function test_validate_recovery_codes_regeneration_token_returns_false_for_invalid_token(): void
    {
        // Arrange
        $token = bin2hex(random_bytes(32));
        $this->user->forceFill([
            'recovery_codes_regeneration_token' => $token,
            'recovery_codes_regeneration_expires_at' => now()->addHour(),
        ])->save();

        // Act
        $result = $this->user->validateRecoveryCodesRegenerationToken('invalid_token');

        // Assert
        $this->assertFalse($result);
    }

    public function test_validate_recovery_codes_regeneration_token_returns_false_for_expired_token(): void
    {
        // Arrange
        $token = bin2hex(random_bytes(32));
        $this->user->forceFill([
            'recovery_codes_regeneration_token' => $token,
            'recovery_codes_regeneration_expires_at' => now()->subHour(),
        ])->save();

        // Act
        $result = $this->user->validateRecoveryCodesRegenerationToken($token);

        // Assert
        $this->assertFalse($result);
    }

    public function test_clear_recovery_codes_regeneration_token(): void
    {
        // Arrange
        $this->user->forceFill([
            'recovery_codes_regeneration_token' => bin2hex(random_bytes(32)),
            'recovery_codes_regeneration_expires_at' => now()->addHour(),
        ])->save();

        // Act
        $this->user->forceFill([
            'recovery_codes_regeneration_token' => null,
            'recovery_codes_regeneration_expires_at' => null,
        ])->save();

        // Assert
        $this->user->refresh();
        $this->assertNull($this->user->recovery_codes_regeneration_token);
        $this->assertNull($this->user->recovery_codes_regeneration_expires_at);
    }

    public function test_get_auth_identifier_name_returns_id(): void
    {
        // Act
        $identifier = $this->user->getAuthIdentifierName();

        // Assert
        $this->assertEquals('id', $identifier);
    }

    public function test_get_auth_display_name_returns_full_name(): void
    {
        // Act
        $displayName = $this->user->getAuthDisplayName();

        // Assert
        $this->assertEquals('John Doe', $displayName);
    }

    public function test_get_auth_display_name_falls_back_to_username(): void
    {
        // Arrange
        $user = User::factory()->create([
            'first_name' => null,
            'last_name' => null,
            'username' => 'testuser',
        ]);

        // Act
        $displayName = $user->getAuthDisplayName();

        // Assert
        $this->assertEquals('testuser', $displayName);
    }

    public function test_web_authn_data_returns_correct_data(): void
    {
        // Arrange
        UserEmail::create([
            'user_id' => $this->user->id,
            'email' => 'john@example.com',
            'is_primary' => true,
            'verified_at' => now(),
        ]);

        // Act
        $webAuthnData = $this->user->webAuthnData();

        // Assert
        $this->assertInstanceOf(WebAuthnData::class, $webAuthnData);
    }

    public function test_get_direct_permissions_returns_permission_collection(): void
    {
        // Arrange
        $permission1 = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $permission2 = Permission::create(['name' => 'delete posts', 'guard_name' => 'web']);
        $this->user->givePermissionTo($permission1, $permission2);

        // Act
        $permissions = $this->user->getDirectPermissions();

        // Assert
        $this->assertCount(2, $permissions);
        $this->assertTrue($permissions->contains($permission1));
        $this->assertTrue($permissions->contains($permission2));
    }

    public function test_get_permissions_via_roles_returns_permission_collection(): void
    {
        // Arrange
        $role = Role::create(['name' => 'editor']);
        $permission1 = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $permission2 = Permission::create(['name' => 'publish posts', 'guard_name' => 'web']);
        $role->givePermissionTo($permission1, $permission2);
        $this->user->assignRole($role);

        // Act
        $permissions = $this->user->getPermissionsViaRoles();

        // Assert
        $this->assertCount(2, $permissions);
        $this->assertTrue($permissions->contains($permission1));
        $this->assertTrue($permissions->contains($permission2));
    }

    public function test_get_all_permissions_returns_combined_permission_collection(): void
    {
        // Arrange
        $role = Role::create(['name' => 'editor']);
        $rolePermission = Permission::create(['name' => 'edit posts', 'guard_name' => 'web']);
        $directPermission = Permission::create(['name' => 'manage settings', 'guard_name' => 'web']);
        $role->givePermissionTo($rolePermission);
        $this->user->assignRole($role);
        $this->user->givePermissionTo($directPermission);

        // Act
        $permissions = $this->user->getAllPermissions();

        // Assert
        $this->assertCount(2, $permissions);
        $this->assertTrue($permissions->contains($rolePermission));
        $this->assertTrue($permissions->contains($directPermission));
    }

    public function test_casts_preferences_to_array(): void
    {
        // Arrange
        $user = User::factory()->create([
            'preferences' => ['theme' => 'dark', 'notifications' => true],
        ]);

        // Act
        $preferences = $user->preferences;

        // Assert
        $this->assertIsArray($preferences);
        $this->assertEquals('dark', $preferences['theme']);
    }

    public function test_casts_social_links_to_array(): void
    {
        // Arrange
        $user = User::factory()->create([
            'social_links' => ['twitter' => '@user', 'github' => 'user'],
        ]);

        // Act
        $socialLinks = $user->social_links;

        // Assert
        $this->assertIsArray($socialLinks);
        $this->assertEquals('@user', $socialLinks['twitter']);
    }

    public function test_casts_is_active_to_boolean(): void
    {
        // Arrange
        $user = User::factory()->create(['is_active' => 1]);

        // Act
        $isActive = $user->is_active;

        // Assert
        $this->assertIsBool($isActive);
        $this->assertTrue($isActive);
    }

    public function test_casts_last_login_at_to_datetime(): void
    {
        // Arrange
        $now = now();
        $user = User::factory()->create(['last_login_at' => $now]);

        // Act
        $lastLoginAt = $user->last_login_at;

        // Assert
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $lastLoginAt);
    }
}
