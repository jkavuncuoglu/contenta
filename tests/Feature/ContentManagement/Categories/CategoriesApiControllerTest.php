<?php

declare(strict_types=1);

use App\Domains\ContentManagement\Categories\Models\Category;
use App\Domains\Security\UserManagement\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('api index returns paginated categories', function () {
    Category::factory()->count(20)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index'));

    $response->assertOk();
    $response->assertJsonStructure([
        'data',
        'meta' => ['current_page', 'last_page', 'per_page', 'total'],
    ]);
    $response->assertJson([
        'meta' => ['total' => 20],
    ]);
});

test('api index respects per_page parameter', function () {
    Category::factory()->count(30)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index', ['per_page' => 10]));

    $response->assertOk();
    $response->assertJson([
        'meta' => [
            'per_page' => 10,
            'total' => 30,
        ],
    ]);
});

test('api index orders categories by name', function () {
    Category::factory()->create(['name' => 'Zebra']);
    Category::factory()->create(['name' => 'Apple']);
    Category::factory()->create(['name' => 'Mango']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index'));

    $response->assertOk();
    $json = $response->json();
    expect($json['data'][0]['name'])->toBe('Apple');
    expect($json['data'][1]['name'])->toBe('Mango');
    expect($json['data'][2]['name'])->toBe('Zebra');
});

test('api index can search categories by name', function () {
    Category::factory()->create(['name' => 'Technology']);
    Category::factory()->create(['name' => 'Health']);
    Category::factory()->create(['name' => 'Technology News']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index', ['search' => 'Technology']));

    $response->assertOk();
    $json = $response->json();
    expect($json['meta']['total'])->toBe(2);
});

test('api index search is case insensitive', function () {
    Category::factory()->create(['name' => 'Technology']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index', ['search' => 'tech']));

    $response->assertOk();
    $json = $response->json();
    expect($json['meta']['total'])->toBe(1);
});

test('api index returns only id name and slug', function () {
    Category::factory()->create([
        'name' => 'Test',
        'slug' => 'test',
        'description' => 'Should not be in API response',
    ]);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index'));

    $response->assertOk();
    $json = $response->json();
    expect($json['data'][0])->toHaveKeys(['id', 'name', 'slug']);
    expect($json['data'][0])->not->toHaveKey('description');
});

test('api index returns empty data when no categories exist', function () {
    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index'));

    $response->assertOk();
    $response->assertJson([
        'data' => [],
        'meta' => ['total' => 0],
    ]);
});

test('api index search returns empty when no matches', function () {
    Category::factory()->create(['name' => 'Technology']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index', ['search' => 'NonExistent']));

    $response->assertOk();
    $response->assertJson([
        'data' => [],
        'meta' => ['total' => 0],
    ]);
});

test('unauthorized user cannot access categories api', function () {
    $response = $this->getJson(route('admin.api.categories.index'));

    $response->assertUnauthorized();
});

test('api index uses default per_page of 50 when not specified', function () {
    Category::factory()->count(60)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('admin.api.categories.index'));

    $response->assertOk();
    $response->assertJson([
        'meta' => [
            'per_page' => 50,
        ],
    ]);
});
