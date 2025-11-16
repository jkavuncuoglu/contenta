<?php

declare(strict_types=1);

use App\Domains\ContentManagement\Tags\Models\Tag;
use App\Domains\Security\UserManagement\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('api index returns paginated tags', function () {
    Tag::factory()->count(20)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index'));

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
    Tag::factory()->count(30)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index', ['per_page' => 10]));

    $response->assertOk();
    $response->assertJson([
        'meta' => [
            'per_page' => 10,
            'total' => 30,
        ],
    ]);
});

test('api index orders tags by name', function () {
    Tag::factory()->create(['name' => 'Zebra']);
    Tag::factory()->create(['name' => 'Apple']);
    Tag::factory()->create(['name' => 'Mango']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index'));

    $response->assertOk();
    $json = $response->json();
    expect($json['data'][0]['name'])->toBe('Apple');
    expect($json['data'][1]['name'])->toBe('Mango');
    expect($json['data'][2]['name'])->toBe('Zebra');
});

test('api index can search tags by name', function () {
    Tag::factory()->create(['name' => 'Laravel']);
    Tag::factory()->create(['name' => 'PHP']);
    Tag::factory()->create(['name' => 'Laravel News']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index', ['search' => 'Laravel']));

    $response->assertOk();
    $json = $response->json();
    expect($json['meta']['total'])->toBe(2);
});

test('api index search is case insensitive', function () {
    Tag::factory()->create(['name' => 'Laravel']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index', ['search' => 'laravel']));

    $response->assertOk();
    $json = $response->json();
    expect($json['meta']['total'])->toBe(1);
});

test('api index returns only id name and slug', function () {
    Tag::factory()->create([
        'name' => 'Test',
        'slug' => 'test',
        'description' => 'Should not be in API response',
    ]);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index'));

    $response->assertOk();
    $json = $response->json();
    expect($json['data'][0])->toHaveKeys(['id', 'name', 'slug']);
    expect($json['data'][0])->not->toHaveKey('description');
});

test('api index returns empty data when no tags exist', function () {
    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index'));

    $response->assertOk();
    $response->assertJson([
        'data' => [],
        'meta' => ['total' => 0],
    ]);
});

test('api index search returns empty when no matches', function () {
    Tag::factory()->create(['name' => 'Laravel']);

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index', ['search' => 'NonExistent']));

    $response->assertOk();
    $response->assertJson([
        'data' => [],
        'meta' => ['total' => 0],
    ]);
});

test('unauthorized user cannot access tags api', function () {
    $response = $this->getJson(route('api.admin.tags.index'));

    $response->assertUnauthorized();
});

test('api index uses default per_page of 50 when not specified', function () {
    Tag::factory()->count(60)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index'));

    $response->assertOk();
    $response->assertJson([
        'meta' => [
            'per_page' => 50,
        ],
    ]);
});

test('api index handles empty search parameter', function () {
    Tag::factory()->count(5)->create();

    $response = $this
        ->actingAs($this->user)
        ->getJson(route('api.admin.tags.index', ['search' => '']));

    $response->assertOk();
    $response->assertJson([
        'meta' => ['total' => 5],
    ]);
});
