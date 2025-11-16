<?php

declare(strict_types=1);

use App\Domains\ContentManagement\Posts\Models\Comment;
use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\Security\UserManagement\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->post = Post::factory()->create();
});

test('index displays paginated comments', function () {
    Comment::factory()->count(25)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.comments.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/Comments')
        ->has('comments')
        ->has('pagination')
        ->has('statistics')
        ->where('pagination.total', 25)
    );
});

test('index filters by status', function () {
    Comment::factory()->pending()->count(5)->create();
    Comment::factory()->approved()->count(10)->create();
    Comment::factory()->spam()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.comments.index', ['status' => 'approved']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('pagination.total', 10)
        ->where('filters.status', 'approved')
    );
});

test('index filters by search', function () {
    Comment::factory()->create([
        'author_name' => 'John Doe',
        'content' => 'Great article!',
    ]);
    Comment::factory()->create([
        'author_name' => 'Jane Smith',
        'content' => 'Nice post about Laravel',
    ]);
    Comment::factory()->count(3)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.comments.index', ['search' => 'Laravel']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('pagination.total', 1)
        ->where('filters.search', 'Laravel')
    );
});

test('index filters by post_id', function () {
    $targetPost = Post::factory()->create();
    Comment::factory()->count(5)->create(['post_id' => $targetPost->id]);
    Comment::factory()->count(10)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.comments.index', ['post_id' => $targetPost->id]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('pagination.total', 5)
        ->where('filters.post_id', (string) $targetPost->id)
    );
});

test('show returns comment details', function () {
    $comment = Comment::factory()->create([
        'post_id' => $this->post->id,
        'author_name' => 'Test Author',
        'content' => 'Test comment content',
    ]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.comments.show', $comment->id));

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'comment' => [
            'id' => $comment->id,
            'author_name' => 'Test Author',
            'content' => 'Test comment content',
        ],
    ]);
});

test('show returns 404 for nonexistent comment', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.comments.show', 99999));

    $response->assertNotFound();
    $response->assertJson([
        'success' => false,
        'message' => 'Comment not found',
    ]);
});

test('update_status changes comment status', function () {
    $comment = Comment::factory()->pending()->create();

    $response = $this
        ->actingAs($this->user)
        ->patch(route('admin.comments.update-status', $comment->id), [
            'status' => 'approved',
        ]);

    $response->assertRedirect(route('admin.comments.index'));
    $response->assertSessionHas('success');

    $comment->refresh();
    expect($comment->status)->toBe('approved');
});

test('update_status validates status values', function () {
    $comment = Comment::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->patch(route('admin.comments.update-status', $comment->id), [
            'status' => 'invalid-status',
        ]);

    $response->assertSessionHasErrors(['status']);
});

test('bulk_update_status updates multiple comments', function () {
    $comments = Comment::factory()->pending()->count(5)->create();
    $ids = $comments->pluck('id')->toArray();

    $response = $this
        ->actingAs($this->user)
        ->patch(route('admin.comments.bulk-update-status'), [
            'ids' => $ids,
            'status' => 'approved',
        ]);

    $response->assertRedirect(route('admin.comments.index'));
    $response->assertSessionHas('success');

    foreach ($comments as $comment) {
        $comment->refresh();
        expect($comment->status)->toBe('approved');
    }
});

test('destroy deletes comment', function () {
    $comment = Comment::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.comments.destroy', $comment->id));

    $response->assertRedirect(route('admin.comments.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('comments', [
        'id' => $comment->id,
    ]);
});

test('destroy returns error for nonexistent comment', function () {
    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.comments.destroy', 99999));

    $response->assertRedirect(route('admin.comments.index'));
    $response->assertSessionHas('error');
});
