<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Comments\Tests\UnitTests;

use App\Domains\ContentManagement\Comments\Services\CommentsService;
use App\Domains\ContentManagement\Comments\Services\CommentsServiceContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CommentsServiceContract::class);
    }

    public function test_it_is_bound_to_container(): void
    {
        // Act
        $service = app(CommentsServiceContract::class);

        // Assert
        $this->assertInstanceOf(CommentsService::class, $service);
    }

    public function test_it_is_registered_as_singleton(): void
    {
        // Act
        $service1 = app(CommentsServiceContract::class);
        $service2 = app(CommentsServiceContract::class);

        // Assert
        $this->assertSame($service1, $service2);
    }

    public function test_get_paginated_comments_returns_paginator(): void
    {
        // Arrange
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->count(25)->create();

        // Act
        $result = $this->service->getPaginatedComments([], 20);

        // Assert
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(25, $result->total());
        $this->assertEquals(20, $result->perPage());
    }

    public function test_get_paginated_comments_filters_by_status(): void
    {
        // Arrange
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->pending()->count(5)->create();
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->approved()->count(10)->create();

        // Act
        $result = $this->service->getPaginatedComments(['status' => 'approved'], 20);

        // Assert
        $this->assertEquals(10, $result->total());
    }

    public function test_get_paginated_comments_filters_by_search(): void
    {
        // Arrange
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->create([
            'author_name' => 'John Doe',
            'content' => 'Laravel is awesome',
        ]);
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->create([
            'content' => 'Vue.js rocks',
        ]);

        // Act
        $result = $this->service->getPaginatedComments(['search' => 'Laravel'], 20);

        // Assert
        $this->assertEquals(1, $result->total());
    }

    public function test_get_paginated_comments_filters_by_post_id(): void
    {
        // Arrange
        $post1 = \App\Domains\ContentManagement\Posts\Models\Post::factory()->create();
        $post2 = \App\Domains\ContentManagement\Posts\Models\Post::factory()->create();

        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->count(5)->create(['post_id' => $post1->id]);
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->count(3)->create(['post_id' => $post2->id]);

        // Act
        $result = $this->service->getPaginatedComments(['post_id' => $post1->id], 20);

        // Assert
        $this->assertEquals(5, $result->total());
    }

    public function test_get_comment_by_id_returns_comment(): void
    {
        // Arrange
        $comment = \App\Domains\ContentManagement\Posts\Models\Comment::factory()->create([
            'content' => 'Test comment',
        ]);

        // Act
        $found = $this->service->getCommentById($comment->id);

        // Assert
        $this->assertNotNull($found);
        $this->assertEquals('Test comment', $found->content);
    }

    public function test_get_comment_by_id_returns_null_when_not_found(): void
    {
        // Act
        $found = $this->service->getCommentById(99999);

        // Assert
        $this->assertNull($found);
    }

    public function test_update_status_updates_comment_status(): void
    {
        // Arrange
        $comment = \App\Domains\ContentManagement\Posts\Models\Comment::factory()->pending()->create();

        // Act
        $result = $this->service->updateStatus($comment->id, 'approved');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'status' => 'approved',
        ]);
    }

    public function test_update_status_returns_false_when_comment_not_found(): void
    {
        // Act
        $result = $this->service->updateStatus(99999, 'approved');

        // Assert
        $this->assertFalse($result);
    }

    public function test_bulk_update_status_updates_multiple_comments(): void
    {
        // Arrange
        $comments = \App\Domains\ContentManagement\Posts\Models\Comment::factory()->pending()->count(5)->create();
        $ids = $comments->pluck('id')->toArray();

        // Act
        $count = $this->service->bulkUpdateStatus($ids, 'approved');

        // Assert
        $this->assertEquals(5, $count);
        foreach ($ids as $id) {
            $this->assertDatabaseHas('comments', [
                'id' => $id,
                'status' => 'approved',
            ]);
        }
    }

    public function test_delete_comment_deletes_comment(): void
    {
        // Arrange
        $comment = \App\Domains\ContentManagement\Posts\Models\Comment::factory()->create();

        // Act
        $result = $this->service->deleteComment($comment->id);

        // Assert
        $this->assertTrue($result);
        $this->assertSoftDeleted('comments', [
            'id' => $comment->id,
        ]);
    }

    public function test_delete_comment_returns_false_when_not_found(): void
    {
        // Act
        $result = $this->service->deleteComment(99999);

        // Assert
        $this->assertFalse($result);
    }

    public function test_get_statistics_returns_correct_counts(): void
    {
        // Arrange
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->pending()->count(3)->create();
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->approved()->count(5)->create();
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->spam()->count(2)->create();
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->trash()->count(1)->create();

        // Act
        $stats = $this->service->getStatistics();

        // Assert
        $this->assertEquals(11, $stats['total']);
        $this->assertEquals(3, $stats['pending']);
        $this->assertEquals(5, $stats['approved']);
        $this->assertEquals(2, $stats['spam']);
        $this->assertEquals(1, $stats['trash']);
    }

    public function test_get_post_comments_returns_approved_comments_only(): void
    {
        // Arrange
        $post = \App\Domains\ContentManagement\Posts\Models\Post::factory()->create();
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->approved()->count(5)->create(['post_id' => $post->id]);
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->pending()->count(3)->create(['post_id' => $post->id]);

        // Act
        $comments = $this->service->getPostComments($post->id, 'approved');

        // Assert
        $this->assertCount(5, $comments);
    }

    public function test_get_post_comments_returns_only_top_level_comments(): void
    {
        // Arrange
        $post = \App\Domains\ContentManagement\Posts\Models\Post::factory()->create();
        $parent = \App\Domains\ContentManagement\Posts\Models\Comment::factory()->approved()->create([
            'post_id' => $post->id,
            'parent_id' => null,
        ]);
        \App\Domains\ContentManagement\Posts\Models\Comment::factory()->approved()->create([
            'post_id' => $post->id,
            'parent_id' => $parent->id,
        ]);

        // Act
        $comments = $this->service->getPostComments($post->id, 'approved');

        // Assert
        $this->assertCount(1, $comments);
    }
}
