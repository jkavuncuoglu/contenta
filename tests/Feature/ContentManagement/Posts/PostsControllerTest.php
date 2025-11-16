<?php

declare(strict_types=1);

use App\Domains\ContentManagement\Categories\Models\Category;
use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentManagement\Tags\Models\Tag;
use App\Domains\Security\UserManagement\Models\User;
use Illuminate\Support\Str;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('index displays paginated posts', function () {
    Post::factory()->count(20)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.posts.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/posts/Index')
        ->has('posts')
        ->has('meta')
        ->where('meta.total', 20)
    );
});

test('index respects per_page parameter', function () {
    Post::factory()->count(30)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.posts.index', ['per_page' => 10]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('meta.per_page', 10)
        ->where('meta.total', 30)
    );
});

test('index orders posts by latest first', function () {
    $oldPost = Post::factory()->create(['created_at' => now()->subDays(2)]);
    $newPost = Post::factory()->create(['created_at' => now()]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.posts.index'));

    $response->assertOk();
    $posts = $response->viewData('page')['props']['posts'];
    expect($posts['data'][0]['id'])->toBe($newPost->id);
});

test('index includes author information', function () {
    $author = User::factory()->create(['name' => 'John Doe']);
    Post::factory()->create(['author_id' => $author->id]);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.posts.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('posts.data.0.author')
        ->where('posts.data.0.author.name', 'John Doe')
    );
});

test('calendar renders calendar view', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.posts.calendar'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/posts/Calendar')
    );
});

test('create renders create form', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.posts.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/posts/Create')
    );
});

test('edit renders edit form with post data', function () {
    $category = Category::factory()->create(['name' => 'Tech']);
    $tag = Tag::factory()->create(['name' => 'Laravel']);
    $post = Post::factory()->create([
        'title' => 'Test Post',
        'status' => 'draft',
    ]);
    $post->categories()->attach($category->id);
    $post->tags()->attach($tag->id);

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.posts.edit', $post->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/posts/Edit')
        ->where('post.id', $post->id)
        ->where('post.title', 'Test Post')
        ->has('post.categories')
        ->has('post.tags')
    );
});

test('show renders show view with post id', function () {
    $post = Post::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.posts.show', $post->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/posts/Show')
        ->where('id', $post->id)
    );
});

test('store creates post with valid data', function () {
    $data = [
        'title' => 'New Post',
        'slug' => 'new-post',
        'content_markdown' => '# Hello World',
        'content_html' => '<h1>Hello World</h1>',
        'excerpt' => 'This is a test post',
        'status' => 'draft',
    ];

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), $data);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('posts', [
        'title' => 'New Post',
        'slug' => 'new-post',
        'status' => 'draft',
        'author_id' => $this->user->id,
    ]);
});

test('store generates slug if not provided', function () {
    $data = [
        'title' => 'Auto Generated Slug',
        'content_markdown' => 'Test content',
        'status' => 'draft',
    ];

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), $data);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('posts', [
        'title' => 'Auto Generated Slug',
        'slug' => Str::slug('Auto Generated Slug'),
    ]);
});

test('store sets author_id from authenticated user', function () {
    $data = [
        'title' => 'Test Post',
        'content_markdown' => 'Test content',
        'status' => 'draft',
    ];

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), $data);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('posts', [
        'title' => 'Test Post',
        'author_id' => $this->user->id,
    ]);
});

test('store validates required fields', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), []);

    $response->assertSessionHasErrors(['title', 'status']);
});

test('store validates title max length', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => str_repeat('a', 256),
            'status' => 'draft',
        ]);

    $response->assertSessionHasErrors(['title']);
});

test('store validates unique slug', function () {
    Post::factory()->create(['slug' => 'existing-slug']);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'New Post',
            'slug' => 'existing-slug',
            'status' => 'draft',
        ]);

    $response->assertSessionHasErrors(['slug']);
});

test('store validates status values', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'status' => 'invalid-status',
        ]);

    $response->assertSessionHasErrors(['status']);
});

test('store accepts valid status values', function ($status) {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'content_markdown' => 'Test content',
            'status' => $status,
        ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('posts', ['status' => $status]);
})->with(['draft', 'published', 'scheduled', 'private']);

test('store can attach categories', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'content_markdown' => 'Test content',
            'status' => 'draft',
            'category_ids' => [$category1->id, $category2->id],
        ]);

    $response->assertSessionHasNoErrors();

    $post = Post::where('title', 'Test Post')->first();
    expect($post->categories)->toHaveCount(2);
});

test('store can attach tags', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'content_markdown' => 'Test content',
            'status' => 'draft',
            'tag_ids' => [$tag1->id, $tag2->id],
        ]);

    $response->assertSessionHasNoErrors();

    $post = Post::where('title', 'Test Post')->first();
    expect($post->tags)->toHaveCount(2);
});

test('store redirects to edit page', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'content_markdown' => 'Test content',
            'status' => 'draft',
        ]);

    $post = Post::where('title', 'Test Post')->first();
    $response->assertRedirect(route('admin.posts.edit', $post->id));
    $response->assertSessionHas('success');
});

test('update updates post with valid data', function () {
    $post = Post::factory()->create([
        'title' => 'Old Title',
        'slug' => 'old-title',
        'status' => 'draft',
    ]);

    $data = [
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'content_markdown' => '# Updated Content',
        'excerpt' => 'Updated excerpt',
        'status' => 'published',
    ];

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.posts.update', $post->id), $data);

    $response->assertSessionHasNoErrors();

    $post->refresh();
    expect($post->title)->toBe('Updated Title');
    expect($post->slug)->toBe('updated-title');
    expect($post->status)->toBe('published');
});

test('update generates slug if not provided', function () {
    $post = Post::factory()->create(['title' => 'Old', 'slug' => 'old']);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.posts.update', $post->id), [
            'title' => 'New Title',
            'status' => 'draft',
        ]);

    $response->assertSessionHasNoErrors();

    $post->refresh();
    expect($post->slug)->toBe(Str::slug('New Title'));
});

test('update validates required fields', function () {
    $post = Post::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.posts.update', $post->id), [
            'title' => '',
        ]);

    $response->assertSessionHasErrors(['title', 'status']);
});

test('update validates unique slug except for current post', function () {
    $post1 = Post::factory()->create(['slug' => 'post-1']);
    $post2 = Post::factory()->create(['slug' => 'post-2']);

    // Should fail - slug already exists on another post
    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.posts.update', $post1->id), [
            'title' => 'Updated',
            'slug' => 'post-2',
            'status' => 'draft',
        ]);

    $response->assertSessionHasErrors(['slug']);

    // Should succeed - same slug as current post
    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.posts.update', $post1->id), [
            'title' => 'Updated',
            'slug' => 'post-1',
            'status' => 'draft',
        ]);

    $response->assertSessionHasNoErrors();
});

test('update can sync categories', function () {
    $category1 = Category::factory()->create();
    $category2 = Category::factory()->create();
    $category3 = Category::factory()->create();

    $post = Post::factory()->create();
    $post->categories()->attach([$category1->id, $category2->id]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.posts.update', $post->id), [
            'title' => 'Updated',
            'status' => 'draft',
            'category_ids' => [$category2->id, $category3->id],
        ]);

    $response->assertSessionHasNoErrors();

    $post->refresh();
    expect($post->categories)->toHaveCount(2);
    expect($post->categories->pluck('id')->toArray())->toContain($category2->id, $category3->id);
});

test('update can sync tags', function () {
    $tag1 = Tag::factory()->create();
    $tag2 = Tag::factory()->create();
    $tag3 = Tag::factory()->create();

    $post = Post::factory()->create();
    $post->tags()->attach([$tag1->id, $tag2->id]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.posts.update', $post->id), [
            'title' => 'Updated',
            'status' => 'draft',
            'tag_ids' => [$tag2->id, $tag3->id],
        ]);

    $response->assertSessionHasNoErrors();

    $post->refresh();
    expect($post->tags)->toHaveCount(2);
    expect($post->tags->pluck('id')->toArray())->toContain($tag2->id, $tag3->id);
});

test('update redirects to edit page', function () {
    $post = Post::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.posts.update', $post->id), [
            'title' => 'Updated',
            'status' => 'draft',
        ]);

    $response->assertRedirect(route('admin.posts.edit', $post->id));
    $response->assertSessionHas('success');
});

test('destroy deletes post', function () {
    $post = Post::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.posts.destroy', $post->id));

    $response->assertRedirect(route('admin.posts.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('posts', [
        'id' => $post->id,
    ]);
});

test('destroy returns 404 for non-existent post', function () {
    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.posts.destroy', 99999));

    $response->assertNotFound();
});

test('unauthorized user cannot access post index', function () {
    $response = $this->get(route('admin.posts.index'));

    $response->assertRedirect(route('login'));
});

test('unauthorized user cannot create post', function () {
    $response = $this->post(route('admin.posts.store'), [
        'title' => 'Test',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthorized user cannot update post', function () {
    $post = Post::factory()->create();

    $response = $this->put(route('admin.posts.update', $post->id), [
        'title' => 'Updated',
        'status' => 'draft',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthorized user cannot delete post', function () {
    $post = Post::factory()->create();

    $response = $this->delete(route('admin.posts.destroy', $post->id));

    $response->assertRedirect(route('login'));
});

test('store can save custom fields', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'content_markdown' => 'Test content',
            'status' => 'draft',
            'custom_fields' => [
                'custom_key' => 'custom_value',
            ],
        ]);

    $response->assertSessionHasNoErrors();

    $post = Post::where('title', 'Test Post')->first();
    expect($post->custom_fields)->toBe(['custom_key' => 'custom_value']);
});

test('store can save meta fields', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'content_markdown' => 'Test content',
            'status' => 'draft',
            'meta_title' => 'SEO Title',
            'meta_description' => 'SEO Description',
        ]);

    $response->assertSessionHasNoErrors();

    $post = Post::where('title', 'Test Post')->first();
    expect($post->meta_title)->toBe('SEO Title');
    expect($post->meta_description)->toBe('SEO Description');
});

test('store validates published_at date format', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Test Post',
            'status' => 'published',
            'published_at' => 'invalid-date',
        ]);

    $response->assertSessionHasErrors(['published_at']);
});

test('store can save scheduled post with published_at', function () {
    $futureDate = now()->addDays(7)->toDateTimeString();

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.posts.store'), [
            'title' => 'Scheduled Post',
            'content_markdown' => 'Test content',
            'status' => 'scheduled',
            'published_at' => $futureDate,
        ]);

    $response->assertSessionHasNoErrors();

    $post = Post::where('title', 'Scheduled Post')->first();
    expect($post->published_at)->not->toBeNull();
});
