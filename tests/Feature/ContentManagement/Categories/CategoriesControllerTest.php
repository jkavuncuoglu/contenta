<?php

declare(strict_types=1);

use App\Domains\ContentManagement\Categories\Models\Category;
use App\Domains\Security\UserManagement\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('index displays paginated categories', function () {
    Category::factory()->count(20)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.categories.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/categories/Index')
        ->has('categories')
        ->has('meta')
        ->where('meta.total', 20)
    );
});

test('index respects per_page parameter', function () {
    Category::factory()->count(30)->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.categories.index', ['per_page' => 10]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('meta.per_page', 10)
        ->where('meta.total', 30)
    );
});

test('create renders create form', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.categories.create'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/categories/Create')
    );
});

test('edit renders edit form with category id', function () {
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->get(route('admin.categories.edit', $category->id));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('admin/categories/Edit')
        ->where('id', $category->id)
    );
});

test('store creates category with valid data', function () {
    $data = [
        'name' => 'Technology',
        'slug' => 'technology',
        'description' => 'Tech related content',
        'is_featured' => true,
        'sort_order' => 1,
    ];

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.categories.store'), $data);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('categories', [
        'name' => 'Technology',
        'slug' => 'technology',
        'description' => 'Tech related content',
        'is_featured' => true,
        'sort_order' => 1,
    ]);
});

test('store generates slug if not provided', function () {
    $data = [
        'name' => 'Web Development',
    ];

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.categories.store'), $data);

    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('categories', [
        'name' => 'Web Development',
        'slug' => 'web-development',
    ]);
});

test('store validates required fields', function () {
    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.categories.store'), []);

    $response->assertSessionHasErrors(['name']);
});

test('store validates unique slug', function () {
    Category::factory()->create(['slug' => 'existing-slug']);

    $response = $this
        ->actingAs($this->user)
        ->post(route('admin.categories.store'), [
            'name' => 'New Category',
            'slug' => 'existing-slug',
        ]);

    $response->assertSessionHasErrors(['slug']);
});

test('update updates category with valid data', function () {
    $category = Category::factory()->create([
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
        ->put(route('admin.categories.update', $category->id), $data);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('admin.categories.edit', $category->id));

    $category->refresh();
    expect($category->name)->toBe('Updated Name');
    expect($category->slug)->toBe('updated-name');
    expect($category->description)->toBe('Updated description');
});

test('update generates slug if not provided', function () {
    $category = Category::factory()->create([
        'name' => 'Old Name',
        'slug' => 'old-name',
    ]);

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.categories.update', $category->id), [
            'name' => 'New Name',
        ]);

    $response->assertSessionHasNoErrors();

    $category->refresh();
    expect($category->name)->toBe('New Name');
    expect($category->slug)->toBe('new-name');
});

test('update validates required fields', function () {
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->put(route('admin.categories.update', $category->id), [
            'name' => '',
        ]);

    $response->assertSessionHasErrors(['name']);
});

test('destroy deletes category', function () {
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.categories.destroy', $category->id));

    $response->assertRedirect(route('admin.categories.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('categories', [
        'id' => $category->id,
    ]);
});

test('destroy redirects to index', function () {
    $category = Category::factory()->create();

    $response = $this
        ->actingAs($this->user)
        ->delete(route('admin.categories.destroy', $category->id));

    $response->assertRedirect(route('admin.categories.index'));
});
