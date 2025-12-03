<?php

declare(strict_types=1);

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentStorage\Http\Controllers\Admin\ContentMigrationController;
use App\Domains\ContentStorage\Jobs\MigrateContentJob;
use App\Domains\ContentStorage\Models\ContentMigration;
use App\Domains\ContentStorage\Services\MigrationService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

// Index Tests

test('displays migration wizard page', function () {
    $response = $this->get(route('admin.settings.content-storage.migrate.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('admin/settings/content-storage/Migrate')
        ->has('recentMigrations')
        ->has('availableDrivers')
    );
});

test('shows recent migrations on index', function () {
    $migration = ContentMigration::create([
        'content_type' => 'pages',
        'from_driver' => 'database',
        'to_driver' => 'local',
        'status' => 'completed',
        'total_items' => 10,
        'migrated_items' => 10,
        'failed_items' => 0,
    ]);

    $response = $this->get(route('admin.settings.content-storage.migrate.index'));

    $response->assertInertia(fn ($page) => $page
        ->has('recentMigrations', 1)
        ->where('recentMigrations.0.content_type', 'pages')
        ->where('recentMigrations.0.status', 'completed')
    );
});

// Store Tests

test('starts async migration', function () {
    Queue::fake();

    Post::factory()->count(5)->create();

    $response = $this->post(route('admin.settings.content-storage.migrations.store'), [
        'content_type' => 'posts',
        'from_driver' => 'database',
        'to_driver' => 'local',
        'delete_source' => false,
        'async' => true,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Migration started in background',
    ]);

    Queue::assertPushed(MigrateContentJob::class);
});

test('executes synchronous migration', function () {
    Post::factory()->count(3)->create();

    $response = $this->post(route('admin.settings.content-storage.migrations.store'), [
        'content_type' => 'posts',
        'from_driver' => 'database',
        'to_driver' => 'local',
        'delete_source' => false,
        'async' => false,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Migration completed successfully',
    ]);

    $this->assertDatabaseHas('content_migrations', [
        'content_type' => 'posts',
        'from_driver' => 'database',
        'to_driver' => 'local',
        'status' => 'completed',
    ]);
});

test('validates migration parameters', function () {
    $response = $this->post(route('admin.settings.content-storage.migrations.store'), [
        'content_type' => 'invalid',
        'from_driver' => 'database',
        'to_driver' => 'local',
    ]);

    $response->assertSessionHasErrors('content_type');
});

test('prevents migration to same driver', function () {
    $response = $this->post(route('admin.settings.content-storage.migrations.store'), [
        'content_type' => 'posts',
        'from_driver' => 'database',
        'to_driver' => 'database',
    ]);

    $response->assertStatus(400);
    $response->assertJson([
        'success' => false,
        'message' => 'Source and destination drivers must be different',
    ]);
});

// Show Tests

test('shows migration status', function () {
    $migration = ContentMigration::create([
        'content_type' => 'pages',
        'from_driver' => 'database',
        'to_driver' => 'local',
        'status' => 'running',
        'total_items' => 100,
        'migrated_items' => 50,
        'failed_items' => 2,
    ]);

    $response = $this->get(route('admin.settings.content-storage.migrations.show', $migration->id));

    $response->assertStatus(200);
    $response->assertJson([
        'id' => $migration->id,
        'status' => 'running',
        'progress' => 50,
        'migrated_items' => 50,
        'failed_items' => 2,
    ]);
});

test('returns 404 for non-existent migration', function () {
    $response = $this->get(route('admin.settings.content-storage.migrations.show', 999));

    $response->assertStatus(404);
});

// List Tests

test('lists all migrations with pagination', function () {
    ContentMigration::factory()->count(25)->create();

    $response = $this->get(route('admin.settings.content-storage.migrations.list'));

    $response->assertStatus(200);
    $response->assertJsonCount(20, 'data'); // Default pagination is 20
});

// Verify Tests

test('verifies migration integrity', function () {
    $migration = ContentMigration::create([
        'content_type' => 'posts',
        'from_driver' => 'database',
        'to_driver' => 'local',
        'status' => 'completed',
        'total_items' => 10,
        'migrated_items' => 10,
        'failed_items' => 0,
    ]);

    $response = $this->post(route('admin.settings.content-storage.migrations.verify', $migration->id), [
        'sample_size' => 5,
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);
    $response->assertJsonStructure([
        'result' => [
            'verified',
            'mismatched',
            'missing',
        ],
    ]);
});

test('validates sample size in verification', function () {
    $migration = ContentMigration::factory()->create();

    $response = $this->post(route('admin.settings.content-storage.migrations.verify', $migration->id), [
        'sample_size' => 2000, // Max is 1000
    ]);

    $response->assertSessionHasErrors('sample_size');
});

// Rollback Tests

test('creates rollback migration', function () {
    Queue::fake();

    $migration = ContentMigration::create([
        'content_type' => 'posts',
        'from_driver' => 'database',
        'to_driver' => 'local',
        'status' => 'completed',
        'total_items' => 10,
        'migrated_items' => 10,
        'failed_items' => 0,
    ]);

    $response = $this->post(route('admin.settings.content-storage.migrations.rollback', $migration->id));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => 'Rollback started',
    ]);

    Queue::assertPushed(MigrateContentJob::class);

    $this->assertDatabaseHas('content_migrations', [
        'content_type' => 'posts',
        'from_driver' => 'local', // Reversed
        'to_driver' => 'database', // Reversed
    ]);
});

// Authentication Tests

test('requires authentication for migration routes', function () {
    auth()->logout();

    $this->get(route('admin.settings.content-storage.migrate.index'))
        ->assertRedirect('/login');

    $this->post(route('admin.settings.content-storage.migrations.store'), [])
        ->assertRedirect('/login');

    $this->get(route('admin.settings.content-storage.migrations.list'))
        ->assertRedirect('/login');
});
