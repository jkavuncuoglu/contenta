<?php

declare(strict_types=1);

use App\Domains\ContentManagement\Categories\Models\Category;
use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\Security\UserManagement\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('api index returns paginated posts', function () {
    Post::factory()->count(20)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.index'));

    $response->assertOk();
    $response->assertJsonStructure([
        'data',
        'meta' => ['current_page', 'last_page', 'per_page', 'total'],
    ]);
    $response->assertJson(['meta' => ['total' => 20]]);
});

test('api index respects per_page parameter', function () {
    Post::factory()->count(30)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.index', ['per_page' => 10]));

    $response->assertOk();
    $response->assertJson(['meta' => ['per_page' => 10, 'total' => 30]]);
});

test('api index can search posts by title', function () {
    Post::factory()->create(['title' => 'Laravel Tutorial']);
    Post::factory()->create(['title' => 'PHP Guide']);
    Post::factory()->create(['title' => 'Laravel Tips']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.index', ['search' => 'Laravel']));

    $response->assertOk();
    $json = $response->json();
    expect($json['meta']['total'])->toBe(2);
});

test('api index can filter by status', function () {
    Post::factory()->create(['status' => 'draft']);
    Post::factory()->count(2)->create(['status' => 'published']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.index', ['status' => 'published']));

    $response->assertOk();
    $json = $response->json();
    expect($json['meta']['total'])->toBe(2);
});

test('calendar requires start_date and end_date', function () {
    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.calendar'));

    $response->assertStatus(422);
    $response->assertJson(['message' => 'start_date and end_date are required']);
});

test('calendar validates date format', function () {
    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.calendar', [
            'start_date' => 'invalid',
            'end_date' => 'invalid',
        ]));

    $response->assertStatus(422);
    $response->assertJson(['message' => 'Invalid date format']);
});

test('calendar returns posts in date range', function () {
    $category = Category::factory()->create();
    $post1 = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDays(5),
    ]);
    $post1->categories()->attach($category->id);

    Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->addDays(10),
    ]);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.calendar', [
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

    $response->assertOk();
    $json = $response->json();
    expect($json['data'])->toHaveCount(1);
    expect($json['data'][0])->toHaveKeys(['id', 'title', 'slug', 'status', 'published_at', 'author', 'categories']);
});

test('scheduled returns paginated scheduled posts', function () {
    Post::factory()->count(3)->create([
        'status' => 'scheduled',
        'published_at' => now()->addDays(1),
    ]);
    Post::factory()->create(['status' => 'draft']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.scheduled'));

    $response->assertOk();
    $response->assertJson(['meta' => ['total' => 3]]);
});

test('reschedule validates published_at is required', function () {
    $post = Post::factory()->create(['status' => 'scheduled']);

    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.admin.posts.reschedule', $post->id), []);

    $response->assertStatus(422);
});

test('reschedule validates published_at is in future', function () {
    $post = Post::factory()->create(['status' => 'scheduled']);

    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.admin.posts.reschedule', $post->id), [
            'published_at' => now()->subDay()->toDateTimeString(),
        ]);

    $response->assertStatus(422);
});

test('reschedule updates post schedule', function () {
    $post = Post::factory()->create([
        'status' => 'scheduled',
        'published_at' => now()->addDays(1),
    ]);

    $newDate = now()->addDays(7)->toDateTimeString();

    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.admin.posts.reschedule', $post->id), [
            'published_at' => $newDate,
        ]);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Post rescheduled successfully',
        'data' => ['id' => $post->id, 'status' => 'scheduled'],
    ]);
});

test('archived returns paginated archived posts', function () {
    Post::factory()->count(2)->create()->each->delete();
    Post::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.posts.archived'));

    $response->assertOk();
    $response->assertJson(['meta' => ['total' => 2]]);
});

test('restore restores archived post', function () {
    $post = Post::factory()->create();
    $post->delete();

    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.admin.posts.restore', $post->id));

    $response->assertOk();
    $response->assertJson([
        'message' => 'Post restored successfully',
        'data' => ['id' => $post->id, 'status' => 'draft'],
    ]);

    $this->assertDatabaseHas('posts', ['id' => $post->id, 'deleted_at' => null]);
});

test('change status validates status field', function () {
    $post = Post::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.admin.posts.change-status', $post->id), [
            'status' => 'invalid',
        ]);

    $response->assertStatus(422);
});

test('change status updates post status', function () {
    $post = Post::factory()->create(['status' => 'draft']);

    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.admin.posts.change-status', $post->id), [
            'status' => 'published',
        ]);

    $response->assertOk();
    $response->assertJson([
        'message' => 'Post status updated successfully',
        'data' => ['id' => $post->id, 'status' => 'published'],
    ]);

    $post->refresh();
    expect($post->status)->toBe('published');
});

test('change status sets published_at when publishing', function () {
    $post = Post::factory()->create(['status' => 'draft', 'published_at' => null]);

    $response = $this
        ->actingAs($this->user)
        ->postJson(route('api.admin.posts.change-status', $post->id), [
            'status' => 'published',
        ]);

    $response->assertOk();
    $post->refresh();
    expect($post->published_at)->not->toBeNull();
});

test('unauthorized user cannot access posts api', function () {
    $response = $this->getJson(route('api.admin.posts.index'));
    $response->assertUnauthorized();
});
