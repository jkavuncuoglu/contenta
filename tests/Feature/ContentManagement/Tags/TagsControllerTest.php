<?php

declare(strict_types=1);

use App\Domains\ContentManagement\Tags\Models\Tag;
use App\Domains\Security\UserManagement\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('index displays paginated tags', function () {
    Tag::factory()->count(20)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.tags.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/tags/Index')
        ->has('tags')
        ->has('meta')
        ->where('meta.total', 20)
    );
});

test('index respects per_page parameter', function () {
    Tag::factory()->count(30)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.tags.index', ['per_page' => 10]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('meta.per_page', 10)
        ->where('meta.total', 30)
    );
});

test('create renders create form', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.tags.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/tags/Create')
    );
});

test('edit renders edit form with tag id', function () {
    $tag = Tag::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.tags.edit', $tag->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/content/tags/Edit')
        ->where('id', $tag->id)
    );
});

test('store creates tag with valid data', function () {
    $data = [
        'name' => 'Laravel',
        'slug' => 'laravel',
        'description' => 'Laravel framework',
        'color' => '#FF2D20',
    ];

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.tags.store'), $data);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('tags', [
        'name' => 'Laravel',
        'slug' => 'laravel',
        'description' => 'Laravel framework',
        'color' => '#FF2D20',
    ]);
});

test('store generates slug if not provided', function () {
    $data = [
        'name' => 'Vue.js Development',
    ];

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.tags.store'), $data);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tags', [
        'name' => 'Vue.js Development',
        'slug' => 'vuejs-development',
    ]);
});

test('store validates required fields', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.tags.store'), []);

    $response->assertSessionHasErrors(['name']);
});

test('store validates unique slug', function () {
    Tag::factory()->create(['slug' => 'existing-slug']);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.tags.store'), [
            'name' => 'New Tag',
            'slug' => 'existing-slug',
        ]);

    $response->assertSessionHasErrors(['slug']);
});

test('update updates tag with valid data', function () {
    $tag = Tag::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $data = [
        'name' => 'Updated Name',
        'slug' => 'updated-name',
        'description' => 'Updated description',
    ];

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.tags.update', $tag->id), $data);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.tags.edit', $tag->id));

    $tag->refresh();
    expect($tag->name)->toBe('Updated Name');
    expect($tag->slug)->toBe('updated-name');
    expect($tag->description)->toBe('Updated description');
});

test('update generates slug if not provided', function () {
    $tag = Tag::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.tags.update', $tag->id), [
            'name' => 'New Name',
        ]);

    $response->assertSessionHasNoErrors();

    $tag->refresh();
    expect($tag->name)->toBe('New Name');
    expect($tag->slug)->toBe('new-name');
});

test('update validates required fields', function () {
    $tag = Tag::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.tags.update', $tag->id), [
            'name' => '',
        ]);

    $response->assertSessionHasErrors(['name']);
});

test('destroy deletes tag', function () {
    $tag = Tag::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.tags.destroy', $tag->id));

    $response->assertRedirect(route('admin.tags.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('tags', [
        'id' => $tag->id,
    ]);
});

test('destroy redirects to index', function () {
    $tag = Tag::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.tags.destroy', $tag->id));

    $response->assertRedirect(route('admin.tags.index'));
});
