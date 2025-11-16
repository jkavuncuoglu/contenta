<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Posts\Tests\UnitTests;

use App\Domains\ContentManagement\Posts\Models\Comment;
use App\Domains\ContentManagement\Posts\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_has_fillable_attributes(): void
    {
        // Arrange
        $post = Post::factory()->create();
        $data = [
            'post_id' => $post->id,
            'parent_id' => null,
            'author_name' => 'John Doe',
            'author_email' => 'john@example.com',
            'author_url' => 'https://example.com',
            'author_ip' => '127.0.0.1',
            'content' => 'Great article!',
            'status' => 'approved',
        ];

        // Act
        $comment = Comment::create($data);

        // Assert
        $this->assertEquals('John Doe', $comment->author_name);
        $this->assertEquals('john@example.com', $comment->author_email);
        $this->assertEquals('Great article!', $comment->content);
        $this->assertEquals('approved', $comment->status);
    }

    public function test_it_belongs_to_post(): void
    {
        // Arrange
        $post = Post::factory()->create(['title' => 'Test Post']);
        $comment = Comment::factory()->create(['post_id' => $post->id]);

        // Act
        $result = $comment->post;

        // Assert
        $this->assertInstanceOf(Post::class, $result);
        $this->assertEquals('Test Post', $result->title);
    }

    public function test_it_belongs_to_parent_comment(): void
    {
        // Arrange
        $parent = Comment::factory()->create(['content' => 'Parent comment']);
        $reply = Comment::factory()->create([
            'parent_id' => $parent->id,
            'content' => 'Reply comment',
        ]);

        // Act
        $result = $reply->parent;

        // Assert
        $this->assertInstanceOf(Comment::class, $result);
        $this->assertEquals('Parent comment', $result->content);
    }

    public function test_it_has_many_replies(): void
    {
        // Arrange
        $parent = Comment::factory()->create();
        Comment::factory()->count(3)->create(['parent_id' => $parent->id]);

        // Act
        $replies = $parent->replies;

        // Assert
        $this->assertCount(3, $replies);
    }

    public function test_approved_scope_returns_only_approved_comments(): void
    {
        // Arrange
        Comment::factory()->approved()->count(5)->create();
        Comment::factory()->pending()->count(3)->create();

        // Act
        $approved = Comment::approved()->get();

        // Assert
        $this->assertCount(5, $approved);
    }

    public function test_pending_scope_returns_only_pending_comments(): void
    {
        // Arrange
        Comment::factory()->pending()->count(4)->create();
        Comment::factory()->approved()->count(2)->create();

        // Act
        $pending = Comment::pending()->get();

        // Assert
        $this->assertCount(4, $pending);
    }

    public function test_spam_scope_returns_only_spam_comments(): void
    {
        // Arrange
        Comment::factory()->spam()->count(2)->create();
        Comment::factory()->approved()->count(5)->create();

        // Act
        $spam = Comment::spam()->get();

        // Assert
        $this->assertCount(2, $spam);
    }

    public function test_parent_scope_returns_only_top_level_comments(): void
    {
        // Arrange
        Comment::factory()->count(3)->create(['parent_id' => null]);
        $parent = Comment::factory()->create();
        Comment::factory()->count(2)->create(['parent_id' => $parent->id]);

        // Act
        $topLevel = Comment::query()->parent()->get();

        // Assert
        $this->assertCount(4, $topLevel);
    }

    public function test_it_uses_soft_deletes(): void
    {
        // Arrange
        $comment = Comment::factory()->create();
        $id = $comment->id;

        // Act
        $comment->delete();

        // Assert
        $this->assertSoftDeleted('comments', ['id' => $id]);
        $this->assertNotNull(Comment::withTrashed()->find($id)->deleted_at);
    }

    public function test_status_is_cast_to_string(): void
    {
        // Arrange
        $comment = Comment::factory()->create(['status' => 'approved']);

        // Act
        $status = $comment->status;

        // Assert
        $this->assertIsString($status);
        $this->assertEquals('approved', $status);
    }
}
