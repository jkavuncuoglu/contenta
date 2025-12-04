<?php

declare(strict_types=1);

use App\Domains\Settings\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = \App\Models\User::factory()->create();
    $this->actingAs($this->user);
});

// Index Tests

test('displays content storage settings page', function () {
    $response = $this->get(route('admin.settings.content-storage.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/settings/content-storage/Index')
        ->has('settings')
        ->has('availableDrivers')
    );
});

test('shows current driver configuration', function () {
    Setting::set('content_storage', 'pages_storage_driver', 'local');
    Setting::set('content_storage', 'posts_storage_driver', 's3');

    $response = $this->get(route('admin.settings.content-storage.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('settings.pages_storage_driver', 'local')
        ->where('settings.posts_storage_driver', 's3')
    );
});

test('masks sensitive credentials in response', function () {
    Setting::set('content_storage', 's3_key', encrypt('AKIAIOSFODNN7EXAMPLE'));
    Setting::set('content_storage', 's3_secret', encrypt('wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY'));

    $response = $this->get(route('admin.settings.content-storage.index'));

    $response->assertInertia(fn ($page) => $page
        ->where('settings.s3_key', '••••••••')
        ->where('settings.s3_secret', '••••••••')
    );
});

// Update Tests

test('updates storage driver selection', function () {
    $response = $this->put(route('admin.settings.content-storage.update'), [
        'pages_storage_driver' => 'local',
        'posts_storage_driver' => 's3',
    ]);

    $response->assertRedirect(route('admin.settings.content-storage.index'));
    $response->assertSessionHas('success');

    expect(Setting::get('content_storage', 'pages_storage_driver'))->toBe('local');
    expect(Setting::get('content_storage', 'posts_storage_driver'))->toBe('s3');
});

test('updates local filesystem settings', function () {
    $response = $this->put(route('admin.settings.content-storage.update'), [
        'pages_storage_driver' => 'database',
        'posts_storage_driver' => 'database',
        'local_base_path' => 'my-content',
    ]);

    $response->assertRedirect();
    expect(Setting::get('content_storage', 'local_base_path'))->toBe('my-content');
});

test('updates s3 settings', function () {
    $response = $this->put(route('admin.settings.content-storage.update'), [
        'pages_storage_driver' => 'database',
        'posts_storage_driver' => 'database',
        's3_region' => 'us-west-2',
        's3_bucket' => 'my-bucket',
        's3_prefix' => 'content',
        's3_key' => 'AKIAIOSFODNN7EXAMPLE',
        's3_secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
    ]);

    $response->assertRedirect();
    expect(Setting::get('content_storage', 's3_region'))->toBe('us-west-2');
    expect(Setting::get('content_storage', 's3_bucket'))->toBe('my-bucket');
    expect(Setting::get('content_storage', 's3_prefix'))->toBe('content');

    $encryptedKey = Setting::get('content_storage', 's3_key');
    expect(decrypt($encryptedKey))->toBe('AKIAIOSFODNN7EXAMPLE');
});

test('does not update masked credentials', function () {
    Setting::set('content_storage', 's3_secret', encrypt('original-secret'));

    $response = $this->put(route('admin.settings.content-storage.update'), [
        'pages_storage_driver' => 'database',
        'posts_storage_driver' => 'database',
        's3_secret' => '••••••••', // Masked value
    ]);

    $response->assertRedirect();

    $encryptedSecret = Setting::get('content_storage', 's3_secret');
    expect(decrypt($encryptedSecret))->toBe('original-secret');
});

test('updates github settings', function () {
    $response = $this->put(route('admin.settings.content-storage.update'), [
        'pages_storage_driver' => 'database',
        'posts_storage_driver' => 'database',
        'github_owner' => 'myorg',
        'github_repo' => 'content',
        'github_branch' => 'main',
        'github_base_path' => 'docs',
        'github_token' => 'ghp_1234567890abcdef',
    ]);

    $response->assertRedirect();
    expect(Setting::get('content_storage', 'github_owner'))->toBe('myorg');
    expect(Setting::get('content_storage', 'github_repo'))->toBe('content');

    $encryptedToken = Setting::get('content_storage', 'github_token');
    expect(decrypt($encryptedToken))->toBe('ghp_1234567890abcdef');
});

test('validates driver selection', function () {
    $response = $this->put(route('admin.settings.content-storage.update'), [
        'pages_storage_driver' => 'invalid_driver',
        'posts_storage_driver' => 'database',
    ]);

    $response->assertSessionHasErrors('pages_storage_driver');
});

test('requires storage drivers', function () {
    $response = $this->put(route('admin.settings.content-storage.update'), []);

    $response->assertSessionHasErrors(['pages_storage_driver', 'posts_storage_driver']);
});

// Test Connection Tests

test('tests database connection successfully', function () {
    $response = $this->post(route('admin.settings.content-storage.test-connection'), [
        'driver' => 'database',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
});

test('tests local filesystem connection', function () {
    $response = $this->post(route('admin.settings.content-storage.test-connection'), [
        'driver' => 'local',
        'config' => [
            'disk' => 'content',
            'base_path' => '',
        ],
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
});

test('validates driver in test connection', function () {
    $response = $this->post(route('admin.settings.content-storage.test-connection'), [
        'driver' => 'invalid',
    ]);

    $response->assertSessionHasErrors('driver');
});

test('requires authentication for all routes', function () {
    auth()->logout();

    $this->get(route('admin.settings.content-storage.index'))
        ->assertRedirect('/login');

    $this->put(route('admin.settings.content-storage.update'), [])
        ->assertRedirect('/login');

    $this->post(route('admin.settings.content-storage.test-connection'), [])
        ->assertRedirect('/login');
});
