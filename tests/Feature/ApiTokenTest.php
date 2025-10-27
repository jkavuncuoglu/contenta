<?php

use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('user can view api tokens page', function () {
    actingAs($this->user)
        ->get('/user/settings/api-tokens')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/ApiTokens')
            ->has('tokens')
            ->has('availableAbilities')
            ->has('maxTokens')
        );
});

test('user can create api token', function () {
    $response = actingAs($this->user)
        ->post('/user/settings/api-tokens', [
            'name' => 'Test Token',
            'abilities' => ['read', 'write'],
        ]);

    $response->assertSessionHas('plainTextToken');
    $response->assertSessionHas('tokenName', 'Test Token');

    expect($this->user->tokens)->toHaveCount(1);
    expect($this->user->tokens->first()->name)->toBe('Test Token');
    expect($this->user->tokens->first()->abilities)->toBe(['read', 'write']);
});

test('user can create token with full access', function () {
    actingAs($this->user)
        ->post('/user/settings/api-tokens', [
            'name' => 'Full Access Token',
            'abilities' => [],
        ]);

    $token = $this->user->tokens->first();
    expect($token->abilities)->toBe(['*']);
});

test('user cannot create token without name', function () {
    actingAs($this->user)
        ->post('/user/settings/api-tokens', [
            'name' => '',
            'abilities' => ['read'],
        ])
        ->assertSessionHasErrors(['name']);
});

test('user cannot create token with invalid abilities', function () {
    actingAs($this->user)
        ->post('/user/settings/api-tokens', [
            'name' => 'Test Token',
            'abilities' => ['invalid_ability'],
        ])
        ->assertSessionHasErrors(['abilities.0']);
});

test('user can delete specific token', function () {
    $token = $this->user->createToken('Test Token')->accessToken;

    actingAs($this->user)
        ->delete("/user/settings/api-tokens/{$token->id}")
        ->assertSessionHas('success');

    expect($this->user->fresh()->tokens)->toHaveCount(0);
});

test('user can delete all tokens', function () {
    $this->user->createToken('Token 1');
    $this->user->createToken('Token 2');
    $this->user->createToken('Token 3');

    expect($this->user->tokens)->toHaveCount(3);

    actingAs($this->user)
        ->delete('/user/settings/api-tokens')
        ->assertSessionHas('success');

    expect($this->user->fresh()->tokens)->toHaveCount(0);
});

test('user cannot create more than max tokens', function () {
    // Create 10 tokens (max)
    for ($i = 1; $i <= 10; $i++) {
        $this->user->createToken("Token {$i}");
    }

    // Try to create 11th token
    actingAs($this->user)
        ->post('/user/settings/api-tokens', [
            'name' => 'Token 11',
            'abilities' => ['read'],
        ])
        ->assertSessionHas('error');

    expect($this->user->fresh()->tokens)->toHaveCount(10);
});

test('user can use token for api authentication', function () {
    $token = $this->user->createToken('API Token', ['read'])->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])->get('/api/user');

    $response->assertOk();
    $response->assertJson([
        'id' => $this->user->id,
        'email' => $this->user->email,
    ]);
});

test('token with read ability can access read endpoints', function () {
    $token = $this->user->createToken('Read Token', ['read'])->plainTextToken;

    $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])
    ->get('/api/posts')
    ->assertOk();
});

test('token with read ability cannot access write endpoints', function () {
    $token = $this->user->createToken('Read Token', ['read'])->plainTextToken;

    $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])
    ->post('/api/posts')
    ->assertForbidden();
});

test('token with delete ability can access delete endpoints', function () {
    $token = $this->user->createToken('Delete Token', ['delete'])->plainTextToken;

    $this->withHeaders([
        'Authorization' => "Bearer {$token}",
    ])
    ->delete('/api/posts/1')
    ->assertOk();
});

test('full access token can access all endpoints', function () {
    $token = $this->user->createToken('Full Access Token')->plainTextToken;

    // Test read
    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->get('/api/posts')
        ->assertOk();

    // Test write
    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->post('/api/posts')
        ->assertOk();

    // Test delete
    $this->withHeaders(['Authorization' => "Bearer {$token}"])
        ->delete('/api/posts/1')
        ->assertOk();
});

