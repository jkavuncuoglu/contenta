<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Tests\UnitTests;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentManagement\Posts\Services\PostService;
use App\Domains\ContentManagement\Posts\Services\PostServiceContract;
use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PostService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PostServiceContract::class);
    }

    public function test_it_can_get_paginated_posts(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(5)->create(['author_id' => $user->id]);

        // Act
        $result = $this->service->getPaginatedPosts(10);

        // Assert
        $this->assertEquals(5, $result->total());
    }

    public function test_it_can_create_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $data = [
            'title' => 'Test Post',
            'content_markdown' => 'Test content',
            'author_id' => $user->id,
        ];

        // Act
        $post = $this->service->createPost($data);

        // Assert
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Test Post', $post->title);
        $this->assertEquals('test-post', $post->slug);
        $this->assertEquals('draft', $post->status);
    }

    public function test_it_can_update_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['title' => 'Original', 'author_id' => $user->id]);

        // Act
        $updated = $this->service->updatePost($post, ['title' => 'Updated']);

        // Assert
        $this->assertEquals('Updated', $updated->title);
        $this->assertEquals('updated', $updated->slug);
    }

    public function test_it_can_delete_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);

        // Act
        $result = $this->service->deletePost($post);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_it_can_publish_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['status' => 'draft', 'author_id' => $user->id]);

        // Act
        $published = $this->service->publishPost($post);

        // Assert
        $this->assertEquals('published', $published->status);
        $this->assertNotNull($published->published_at);
    }

    public function test_it_can_unpublish_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['status' => 'published', 'author_id' => $user->id]);

        // Act
        $unpublished = $this->service->unpublishPost($post);

        // Assert
        $this->assertEquals('draft', $unpublished->status);
    }

    public function test_it_can_schedule_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['status' => 'draft', 'author_id' => $user->id]);
        $publishAt = now()->addDays(1);

        // Act
        $scheduled = $this->service->schedulePost($post, $publishAt);

        // Assert
        $this->assertEquals('scheduled', $scheduled->status);
        $this->assertEquals($publishAt->timestamp, $scheduled->published_at->timestamp);
    }

    public function test_it_can_get_post_by_slug(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->create([
            'slug' => 'test-slug',
            'status' => 'published',
            'author_id' => $user->id,
        ]);

        // Act
        $post = $this->service->getPostBySlug('test-slug');

        // Assert
        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('test-slug', $post->slug);
    }

    public function test_it_can_duplicate_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $original = Post::factory()->create(['title' => 'Original', 'author_id' => $user->id]);

        // Act
        $duplicate = $this->service->duplicatePost($original, 'Duplicate');

        // Assert
        $this->assertEquals('Duplicate', $duplicate->title);
        $this->assertEquals('duplicate', $duplicate->slug);
        $this->assertEquals('draft', $duplicate->status);
    }

    public function test_it_can_get_published_posts(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(3)->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $user->id,
        ]);
        Post::factory()->create(['status' => 'draft', 'author_id' => $user->id]);

        // Act
        $posts = $this->service->getPublishedPosts(10);

        // Assert
        $this->assertEquals(3, $posts->total());
    }

    public function test_it_can_get_draft_posts(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(2)->create(['status' => 'draft', 'author_id' => $user->id]);
        Post::factory()->create(['status' => 'published', 'author_id' => $user->id]);

        // Act
        $posts = $this->service->getDraftPosts(10);

        // Assert
        $this->assertEquals(2, $posts->total());
    }

    public function test_it_can_attach_categories(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        $categories = \App\Domains\ContentManagement\Categories\Models\Category::factory()->count(3)->create();
        $categoryIds = $categories->pluck('id')->toArray();

        // Act
        $result = $this->service->attachCategories($post, $categoryIds);

        // Assert
        $this->assertCount(3, $result->categories);
        $this->assertEquals($categoryIds, $result->categories->pluck('id')->sort()->values()->toArray());
    }

    public function test_it_can_attach_tags(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        $tags = \App\Domains\ContentManagement\Tags\Models\Tag::factory()->count(3)->create();
        $tagIds = $tags->pluck('id')->toArray();

        // Act
        $result = $this->service->attachTags($post, $tagIds);

        // Assert
        $this->assertCount(3, $result->tags);
        $this->assertEquals($tagIds, $result->tags->pluck('id')->sort()->values()->toArray());
    }

    public function test_it_can_get_calendar_posts(): void
    {
        // Arrange
        $user = User::factory()->create();
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'published',
            'published_at' => now()->addDays(5),
        ]);
        Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'scheduled',
            'published_at' => now()->addDays(10),
        ]);
        Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'published',
            'published_at' => now()->subMonths(2),
        ]);

        // Act
        $posts = $this->service->getCalendarPosts($startDate, $endDate);

        // Assert
        $this->assertCount(2, $posts);
    }

    public function test_it_can_get_scheduled_posts(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(3)->create([
            'author_id' => $user->id,
            'status' => 'scheduled',
            'published_at' => now()->addDays(1),
        ]);
        Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'published',
        ]);

        // Act
        $posts = $this->service->getScheduledPosts(10);

        // Assert
        $this->assertEquals(3, $posts->total());
    }

    public function test_it_can_publish_due_posts(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(2)->create([
            'author_id' => $user->id,
            'status' => 'scheduled',
            'published_at' => now()->subHour(),
        ]);
        Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'scheduled',
            'published_at' => now()->addDay(),
        ]);

        // Act
        $published = $this->service->publishDuePosts();

        // Assert
        $this->assertCount(2, $published);
        foreach ($published as $post) {
            $this->assertEquals('published', $post->status);
        }
    }

    public function test_it_can_get_posts_by_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(4)->create([
            'author_id' => $user->id,
            'status' => 'draft',
        ]);
        Post::factory()->count(2)->create([
            'author_id' => $user->id,
            'status' => 'published',
        ]);

        // Act
        $posts = $this->service->getPostsByStatus('draft', 10);

        // Assert
        $this->assertEquals(4, $posts->total());
    }

    public function test_it_can_get_archived_posts(): void
    {
        // Arrange
        $user = User::factory()->create();
        $deletedPost = Post::factory()->create(['author_id' => $user->id]);
        $deletedPost->delete();
        Post::factory()->count(2)->create(['author_id' => $user->id]);

        // Act
        $posts = $this->service->getArchivedPosts(10);

        // Assert
        $this->assertEquals(1, $posts->total());
    }

    public function test_it_can_restore_post(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        $postId = $post->id;
        $post->delete();

        // Act
        $restored = $this->service->restorePost($postId);

        // Assert
        $this->assertEquals('draft', $restored->status);
        $this->assertNull($restored->deleted_at);
    }

    public function test_it_can_change_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'draft',
        ]);

        // Act
        $updated = $this->service->changeStatus($post, 'published');

        // Assert
        $this->assertEquals('published', $updated->status);
        $this->assertNotNull($updated->published_at);
    }

    public function test_change_status_sets_published_at_when_publishing(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'draft',
            'published_at' => null,
        ]);

        // Act
        $updated = $this->service->changeStatus($post, 'published');

        // Assert
        $this->assertEquals('published', $updated->status);
        $this->assertNotNull($updated->published_at);
    }

    public function test_change_status_does_not_override_existing_published_at(): void
    {
        // Arrange
        $user = User::factory()->create();
        $originalPublishedAt = now()->subDays(5);
        $post = Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'draft',
            'published_at' => $originalPublishedAt,
        ]);

        // Act
        $updated = $this->service->changeStatus($post, 'published');

        // Assert
        $this->assertEquals('published', $updated->status);
        $this->assertEquals($originalPublishedAt->timestamp, $updated->published_at->timestamp);
    }

    // Sad Path Tests

    public function test_get_paginated_posts_returns_empty_when_no_posts_exist(): void
    {
        // Act
        $result = $this->service->getPaginatedPosts(10);

        // Assert
        $this->assertEquals(0, $result->total());
        $this->assertCount(0, $result->items());
    }

    public function test_get_post_by_slug_returns_null_when_slug_not_found(): void
    {
        // Act
        $post = $this->service->getPostBySlug('non-existent-slug');

        // Assert
        $this->assertNull($post);
    }

    public function test_get_published_posts_returns_empty_when_no_published_posts_exist(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['status' => 'draft', 'author_id' => $user->id]);

        // Act
        $posts = $this->service->getPublishedPosts(10);

        // Assert
        $this->assertEquals(0, $posts->total());
    }

    public function test_get_draft_posts_returns_empty_when_no_draft_posts_exist(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(2)->create(['status' => 'published', 'author_id' => $user->id]);

        // Act
        $posts = $this->service->getDraftPosts(10);

        // Assert
        $this->assertEquals(0, $posts->total());
    }

    public function test_attach_categories_with_empty_array_removes_all_categories(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        $categories = \App\Domains\ContentManagement\Categories\Models\Category::factory()->count(3)->create();
        $post->categories()->attach($categories->pluck('id')->toArray());

        // Act
        $result = $this->service->attachCategories($post, []);

        // Assert
        $this->assertCount(0, $result->categories);
    }

    public function test_attach_tags_with_empty_array_removes_all_tags(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);
        $tags = \App\Domains\ContentManagement\Tags\Models\Tag::factory()->count(3)->create();
        $post->tags()->attach($tags->pluck('id')->toArray());

        // Act
        $result = $this->service->attachTags($post, []);

        // Assert
        $this->assertCount(0, $result->tags);
    }

    public function test_get_calendar_posts_returns_empty_when_no_posts_in_date_range(): void
    {
        // Arrange
        $user = User::factory()->create();
        $startDate = now()->addMonths(2)->startOfMonth();
        $endDate = now()->addMonths(2)->endOfMonth();

        Post::factory()->create([
            'author_id' => $user->id,
            'status' => 'published',
            'published_at' => now()->subMonths(1),
        ]);

        // Act
        $posts = $this->service->getCalendarPosts($startDate, $endDate);

        // Assert
        $this->assertCount(0, $posts);
    }

    public function test_get_scheduled_posts_returns_empty_when_no_scheduled_posts_exist(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(3)->create([
            'author_id' => $user->id,
            'status' => 'published',
        ]);

        // Act
        $posts = $this->service->getScheduledPosts(10);

        // Assert
        $this->assertEquals(0, $posts->total());
    }

    public function test_publish_due_posts_returns_empty_when_no_due_posts_exist(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(2)->create([
            'author_id' => $user->id,
            'status' => 'scheduled',
            'published_at' => now()->addDay(),
        ]);

        // Act
        $published = $this->service->publishDuePosts();

        // Assert
        $this->assertCount(0, $published);
    }

    public function test_get_posts_by_status_returns_empty_when_no_posts_with_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(3)->create([
            'author_id' => $user->id,
            'status' => 'published',
        ]);

        // Act
        $posts = $this->service->getPostsByStatus('archived', 10);

        // Assert
        $this->assertEquals(0, $posts->total());
    }

    public function test_get_archived_posts_returns_empty_when_no_archived_posts_exist(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->count(3)->create(['author_id' => $user->id]);

        // Act
        $posts = $this->service->getArchivedPosts(10);

        // Assert
        $this->assertEquals(0, $posts->total());
    }

    public function test_update_post_preserves_slug_when_title_not_changed(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'title' => 'Original',
            'slug' => 'custom-slug',
            'author_id' => $user->id,
        ]);

        // Act
        $updated = $this->service->updatePost($post, ['status' => 'published']);

        // Assert
        $this->assertEquals('custom-slug', $updated->slug);
    }

    public function test_duplicate_post_creates_draft_regardless_of_original_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        $original = Post::factory()->create([
            'title' => 'Published Post',
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $user->id,
        ]);

        // Act
        $duplicate = $this->service->duplicatePost($original, 'Duplicate');

        // Assert
        $this->assertEquals('draft', $duplicate->status);
        $this->assertNull($duplicate->published_at);
    }

    public function test_unpublish_post_clears_published_at_timestamp(): void
    {
        // Arrange
        $user = User::factory()->create();
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $user->id,
        ]);

        // Act
        $unpublished = $this->service->unpublishPost($post);

        // Assert
        $this->assertEquals('draft', $unpublished->status);
        $this->assertNull($unpublished->published_at);
    }

    public function test_create_post_generates_unique_slug_when_duplicate_title_exists(): void
    {
        // Arrange
        $user = User::factory()->create();
        Post::factory()->create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'author_id' => $user->id,
        ]);

        // Act
        $post = $this->service->createPost([
            'title' => 'Test Post',
            'content_markdown' => 'Test content',
            'author_id' => $user->id,
        ]);

        // Assert
        $this->assertNotEquals('test-post', $post->slug);
        $this->assertStringStartsWith('test-post', $post->slug);
    }
}
