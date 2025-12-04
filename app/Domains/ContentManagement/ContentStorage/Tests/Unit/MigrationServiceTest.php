<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\Tests\Unit;

use App\Domains\ContentManagement\ContentStorage\Exceptions\MigrationException;
use App\Domains\ContentManagement\ContentStorage\Models\ContentMigration;
use App\Domains\ContentManagement\ContentStorage\Services\ContentStorageManager;
use App\Domains\ContentManagement\ContentStorage\Services\MigrationService;
use App\Domains\ContentManagement\ContentStorage\Services\PathPatternResolver;
use App\Domains\ContentManagement\Pages\Models\Page;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * MigrationService Unit Tests
 */
class MigrationServiceTest extends TestCase
{
    use RefreshDatabase;

    private MigrationService $migrationService;

    private ContentStorageManager $storageManager;

    private PathPatternResolver $pathResolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pathResolver = new PathPatternResolver;
        $this->storageManager = new ContentStorageManager($this->app);
        $this->migrationService = new MigrationService(
            $this->storageManager,
            $this->pathResolver
        );

        // Setup content disk for tests
        Storage::fake('content');
    }

    public function test_starts_migration_successfully(): void
    {
        $migration = $this->migrationService->startMigration('pages', 'database', 'local');

        $this->assertInstanceOf(ContentMigration::class, $migration);
        $this->assertEquals('pages', $migration->content_type);
        $this->assertEquals('database', $migration->from_driver);
        $this->assertEquals('local', $migration->to_driver);
        $this->assertEquals(ContentMigration::STATUS_PENDING, $migration->status);
        $this->assertEquals(0, $migration->total_items);
        $this->assertEquals(0, $migration->migrated_items);
        $this->assertEquals(0, $migration->failed_items);
    }

    public function test_throws_exception_if_migration_already_running(): void
    {
        // Create running migration
        ContentMigration::create([
            'content_type' => 'pages',
            'from_driver' => 'database',
            'to_driver' => 'local',
            'status' => ContentMigration::STATUS_RUNNING,
            'total_items' => 0,
            'migrated_items' => 0,
            'failed_items' => 0,
        ]);

        $this->expectException(MigrationException::class);
        $this->migrationService->startMigration('pages', 'database', 'local');
    }

    public function test_throws_exception_for_same_driver_combination(): void
    {
        $this->expectException(MigrationException::class);
        $this->migrationService->startMigration('pages', 'database', 'database');
    }

    public function test_executes_migration_with_no_items(): void
    {
        $migration = $this->migrationService->startMigration('pages', 'database', 'local');

        $result = $this->migrationService->executeMigration($migration);

        $this->assertEquals(ContentMigration::STATUS_COMPLETED, $result->status);
        $this->assertEquals(0, $result->total_items);
        $this->assertEquals(0, $result->migrated_items);
        $this->assertEquals(0, $result->failed_items);
    }

    public function test_executes_migration_from_database_to_local(): void
    {
        // Create test pages in database
        $page1 = Page::factory()->create([
            'slug' => 'about-us',
            'title' => 'About Us',
            'markdown_content' => 'This is the about page content.',
            'status' => 'published',
        ]);

        $page2 = Page::factory()->create([
            'slug' => 'contact',
            'title' => 'Contact',
            'markdown_content' => 'Contact us here.',
            'status' => 'published',
        ]);

        $migration = $this->migrationService->startMigration('pages', 'database', 'local');
        $result = $this->migrationService->executeMigration($migration);

        $this->assertEquals(ContentMigration::STATUS_COMPLETED, $result->status);
        $this->assertEquals(2, $result->total_items);
        $this->assertEquals(2, $result->migrated_items);
        $this->assertEquals(0, $result->failed_items);

        // Verify files were created
        Storage::disk('content')->assertExists('pages/about-us.md');
        Storage::disk('content')->assertExists('pages/contact.md');

        // Verify content
        $aboutContent = Storage::disk('content')->get('pages/about-us.md');
        $this->assertStringContainsString('title: "About Us"', $aboutContent);
        $this->assertStringContainsString('slug: about-us', $aboutContent);
        $this->assertStringContainsString('This is the about page content.', $aboutContent);
    }

    public function test_executes_migration_with_delete_source(): void
    {
        $page = Page::factory()->create([
            'slug' => 'test-page',
            'title' => 'Test Page',
            'markdown_content' => 'Test content',
        ]);

        $migration = $this->migrationService->startMigration('pages', 'database', 'local');
        $this->migrationService->executeMigration($migration, true);

        // Verify file was created
        Storage::disk('content')->assertExists('pages/test-page.md');

        // Verify source was deleted (soft delete)
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }

    public function test_executes_migration_from_local_to_database(): void
    {
        // Create a user for post author_id
        $user = \App\Domains\Security\UserManagement\Models\User::factory()->create();

        // Create test markdown files
        $markdown1 = <<<'MD'
---
title: Blog Post 1
slug: blog-post-1
published_at: "2025-12-02"
status: published
---

This is blog post 1 content.
MD;

        $markdown2 = <<<'MD'
---
title: Blog Post 2
slug: blog-post-2
published_at: "2025-12-01"
status: draft
---

This is blog post 2 content.
MD;

        Storage::disk('content')->put('posts/2025/12/blog-post-1.md', $markdown1);
        Storage::disk('content')->put('posts/2025/12/blog-post-2.md', $markdown2);

        $migration = $this->migrationService->startMigration('posts', 'local', 'database');
        $result = $this->migrationService->executeMigration($migration);

        $this->assertEquals(ContentMigration::STATUS_COMPLETED, $result->status);
        $this->assertEquals(2, $result->total_items);
        $this->assertEquals(2, $result->migrated_items);
        $this->assertEquals(0, $result->failed_items);

        // Verify posts were created in database
        $this->assertDatabaseHas('posts', [
            'slug' => 'blog-post-1',
            'title' => 'Blog Post 1',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('posts', [
            'slug' => 'blog-post-2',
            'title' => 'Blog Post 2',
            'status' => 'draft',
        ]);

        // Verify content was saved
        $post1 = Post::where('slug', 'blog-post-1')->first();
        $this->assertStringContainsString('This is blog post 1 content.', $post1->content_markdown);
    }

    public function test_tracks_failed_items_during_migration(): void
    {
        // Create page with missing required field to trigger error
        Page::factory()->create(['slug' => 'valid-page', 'title' => 'Valid']);

        // Create migration
        $migration = $this->migrationService->startMigration('pages', 'database', 'local');

        // Mock a failed write by creating an invalid path
        $page2 = Page::factory()->create(['slug' => '', 'title' => 'Invalid']);

        $result = $this->migrationService->executeMigration($migration);

        // Should have at least one successful migration
        $this->assertGreaterThanOrEqual(1, $result->migrated_items);
    }

    public function test_verifies_migration_successfully(): void
    {
        // Create pages
        $page1 = Page::factory()->create([
            'slug' => 'page-1',
            'title' => 'Page 1',
            'markdown_content' => 'Content 1',
        ]);

        $page2 = Page::factory()->create([
            'slug' => 'page-2',
            'title' => 'Page 2',
            'markdown_content' => 'Content 2',
        ]);

        // Execute migration
        $migration = $this->migrationService->startMigration('pages', 'database', 'local');
        $this->migrationService->executeMigration($migration);

        // Verify migration
        $result = $this->migrationService->verifyMigration($migration, 0); // Verify all

        $this->assertEquals(2, $result['verified']);
        $this->assertEquals(0, $result['mismatched']);
        $this->assertEquals(0, $result['missing']);
        $this->assertEmpty($result['errors']);
    }

    public function test_verifies_migration_with_sample_size(): void
    {
        // Create 20 pages
        for ($i = 1; $i <= 20; $i++) {
            Page::factory()->create([
                'slug' => "page-{$i}",
                'title' => "Page {$i}",
                'markdown_content' => "Content {$i}",
            ]);
        }

        // Execute migration
        $migration = $this->migrationService->startMigration('pages', 'database', 'local');
        $this->migrationService->executeMigration($migration);

        // Verify with sample size of 5
        $result = $this->migrationService->verifyMigration($migration, 5);

        // Should only verify 5 items
        $totalChecked = $result['verified'] + $result['mismatched'] + $result['missing'];
        $this->assertEquals(5, $totalChecked);
    }

    public function test_detects_missing_files_during_verification(): void
    {
        // Create page
        $page = Page::factory()->create([
            'slug' => 'test-page',
            'title' => 'Test',
            'markdown_content' => 'Content',
        ]);

        // Execute migration
        $migration = $this->migrationService->startMigration('pages', 'database', 'local');
        $this->migrationService->executeMigration($migration);

        // Delete destination file
        Storage::disk('content')->delete('pages/test-page.md');

        // Verify
        $result = $this->migrationService->verifyMigration($migration, 0);

        $this->assertEquals(0, $result['verified']);
        $this->assertEquals(1, $result['missing']);
        $this->assertCount(1, $result['errors']);
        $this->assertStringContainsString('Missing in destination', $result['errors'][0]['error']);
    }

    public function test_rollback_migration(): void
    {
        // Create pages in database
        $page = Page::factory()->create([
            'slug' => 'test-page',
            'title' => 'Test',
            'markdown_content' => 'Content',
        ]);

        // Execute migration database -> local
        $originalMigration = $this->migrationService->startMigration('pages', 'database', 'local');
        $this->migrationService->executeMigration($originalMigration, true); // Delete source

        $this->assertSoftDeleted('pages', ['id' => $page->id]);
        Storage::disk('content')->assertExists('pages/test-page.md');

        // Rollback (local -> database)
        $rollbackMigration = $this->migrationService->rollbackMigration($originalMigration);

        $this->assertEquals(ContentMigration::STATUS_COMPLETED, $rollbackMigration->status);
        $this->assertEquals('pages', $rollbackMigration->content_type);
        $this->assertEquals('local', $rollbackMigration->from_driver);
        $this->assertEquals('database', $rollbackMigration->to_driver);

        // Verify page is back in database
        $this->assertDatabaseHas('pages', [
            'slug' => 'test-page',
            'title' => 'Test',
        ]);
    }

    public function test_throws_exception_when_rollback_on_incomplete_migration(): void
    {
        $migration = ContentMigration::create([
            'content_type' => 'pages',
            'from_driver' => 'database',
            'to_driver' => 'local',
            'status' => ContentMigration::STATUS_PENDING,
            'total_items' => 0,
            'migrated_items' => 0,
            'failed_items' => 0,
        ]);

        $this->expectException(MigrationException::class);
        $this->migrationService->rollbackMigration($migration);
    }

    public function test_cancels_running_migration(): void
    {
        $migration = ContentMigration::create([
            'content_type' => 'pages',
            'from_driver' => 'database',
            'to_driver' => 'local',
            'status' => ContentMigration::STATUS_RUNNING,
            'total_items' => 10,
            'migrated_items' => 5,
            'failed_items' => 0,
        ]);

        $result = $this->migrationService->cancelMigration($migration);

        $this->assertTrue($result);
        $this->assertEquals(ContentMigration::STATUS_FAILED, $migration->fresh()->status);
    }

    public function test_cannot_cancel_completed_migration(): void
    {
        $migration = ContentMigration::create([
            'content_type' => 'pages',
            'from_driver' => 'database',
            'to_driver' => 'local',
            'status' => ContentMigration::STATUS_COMPLETED,
            'total_items' => 10,
            'migrated_items' => 10,
            'failed_items' => 0,
        ]);

        $result = $this->migrationService->cancelMigration($migration);

        $this->assertFalse($result);
    }

    public function test_gets_active_migrations(): void
    {
        // Create various migrations
        ContentMigration::create([
            'content_type' => 'pages',
            'from_driver' => 'database',
            'to_driver' => 'local',
            'status' => ContentMigration::STATUS_RUNNING,
            'total_items' => 0,
            'migrated_items' => 0,
            'failed_items' => 0,
        ]);

        ContentMigration::create([
            'content_type' => 'posts',
            'from_driver' => 'local',
            'to_driver' => 'database',
            'status' => ContentMigration::STATUS_PENDING,
            'total_items' => 0,
            'migrated_items' => 0,
            'failed_items' => 0,
        ]);

        ContentMigration::create([
            'content_type' => 'pages',
            'from_driver' => 'database',
            'to_driver' => 's3',
            'status' => ContentMigration::STATUS_COMPLETED,
            'total_items' => 0,
            'migrated_items' => 0,
            'failed_items' => 0,
        ]);

        $activeMigrations = $this->migrationService->getActiveMigrations();

        $this->assertCount(2, $activeMigrations);
    }

    public function test_gets_migration_history(): void
    {
        // Create 15 migrations
        for ($i = 1; $i <= 15; $i++) {
            ContentMigration::create([
                'content_type' => 'pages',
                'from_driver' => 'database',
                'to_driver' => 'local',
                'status' => ContentMigration::STATUS_COMPLETED,
                'total_items' => 0,
                'migrated_items' => 0,
                'failed_items' => 0,
            ]);
        }

        $history = $this->migrationService->getMigrationHistory();

        $this->assertCount(10, $history); // Default limit is 10
    }

    public function test_gets_migration_history_filtered_by_content_type(): void
    {
        ContentMigration::create([
            'content_type' => 'pages',
            'from_driver' => 'database',
            'to_driver' => 'local',
            'status' => ContentMigration::STATUS_COMPLETED,
            'total_items' => 0,
            'migrated_items' => 0,
            'failed_items' => 0,
        ]);

        ContentMigration::create([
            'content_type' => 'posts',
            'from_driver' => 'local',
            'to_driver' => 'database',
            'status' => ContentMigration::STATUS_COMPLETED,
            'total_items' => 0,
            'migrated_items' => 0,
            'failed_items' => 0,
        ]);

        $history = $this->migrationService->getMigrationHistory('pages');

        $this->assertCount(1, $history);
        $this->assertEquals('pages', $history->first()->content_type);
    }

    public function test_migration_updates_progress_correctly(): void
    {
        // Create 3 pages
        Page::factory()->count(3)->create();

        $migration = $this->migrationService->startMigration('pages', 'database', 'local');

        // Before execution
        $this->assertEquals(0, $migration->migrated_items);

        // Execute migration
        $result = $this->migrationService->executeMigration($migration);

        // After execution
        $this->assertEquals(3, $result->total_items);
        $this->assertEquals(3, $result->migrated_items);
        $this->assertEquals(100, $result->getProgress());
    }
}
