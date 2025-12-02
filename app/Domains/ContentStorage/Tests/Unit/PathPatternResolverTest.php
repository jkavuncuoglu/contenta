<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Tests\Unit;

use App\Domains\ContentManagement\Posts\Models\Post;
use App\Domains\ContentStorage\Exceptions\WriteException;
use App\Domains\ContentStorage\Services\PathPatternResolver;
use App\Domains\PageBuilder\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PathPatternResolver Unit Tests
 */
class PathPatternResolverTest extends TestCase
{
    use RefreshDatabase;

    private PathPatternResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new PathPatternResolver;
    }

    public function test_resolves_simple_slug_pattern(): void
    {
        $page = Page::factory()->make(['slug' => 'about-us', 'id' => 1]);

        $path = $this->resolver->resolve('pages/{slug}.md', 'pages', $page);

        $this->assertEquals('pages/about-us.md', $path);
    }

    public function test_resolves_pattern_with_type_and_id(): void
    {
        $page = Page::factory()->make(['id' => 123, 'slug' => 'test']);

        $path = $this->resolver->resolve('{type}/{id}', 'pages', $page);

        $this->assertEquals('pages/123.md', $path);
    }

    public function test_resolves_pattern_with_date_tokens_for_posts(): void
    {
        $post = Post::factory()->make([
            'slug' => 'hello-world',
            'published_at' => '2025-12-02 14:30:00',
        ]);

        $path = $this->resolver->resolve('posts/{year}/{month}/{slug}', 'posts', $post);

        $this->assertEquals('posts/2025/12/hello-world.md', $path);
    }

    public function test_resolves_pattern_with_day_token(): void
    {
        $post = Post::factory()->make([
            'slug' => 'daily-post',
            'published_at' => '2025-12-02',
        ]);

        $path = $this->resolver->resolve('{type}/{year}/{month}/{day}/{slug}', 'posts', $post);

        $this->assertEquals('posts/2025/12/02/daily-post.md', $path);
    }

    public function test_resolves_pattern_with_status_token(): void
    {
        $page = Page::factory()->make(['slug' => 'test', 'status' => 'draft']);

        $path = $this->resolver->resolve('{type}/{status}/{slug}', 'pages', $page);

        $this->assertEquals('pages/draft/test.md', $path);
    }

    public function test_adds_md_extension_if_missing(): void
    {
        $page = Page::factory()->make(['slug' => 'test']);

        $path = $this->resolver->resolve('pages/{slug}', 'pages', $page);

        $this->assertStringEndsWith('.md', $path);
    }

    public function test_does_not_add_duplicate_md_extension(): void
    {
        $page = Page::factory()->make(['slug' => 'test']);

        $path = $this->resolver->resolve('pages/{slug}.md', 'pages', $page);

        $this->assertEquals('pages/test.md', $path);
        $this->assertStringNotContainsString('.md.md', $path);
    }

    public function test_throws_exception_for_directory_traversal(): void
    {
        $page = Page::factory()->make(['slug' => 'test']);

        $this->expectException(WriteException::class);
        $this->expectExceptionMessage('Directory traversal not allowed');

        $this->resolver->resolve('../../../etc/passwd', 'pages', $page);
    }

    public function test_throws_exception_for_absolute_path(): void
    {
        $page = Page::factory()->make(['slug' => 'test']);

        $this->expectException(WriteException::class);
        $this->expectExceptionMessage('Absolute paths not allowed');

        $this->resolver->resolve('/etc/passwd.md', 'pages', $page);
    }

    public function test_throws_exception_for_dangerous_characters(): void
    {
        $page = Page::factory()->make(['slug' => 'test']);

        $this->expectException(WriteException::class);
        $this->expectExceptionMessage('invalid characters');

        $this->resolver->resolve('pages/<script>.md', 'pages', $page);
    }

    public function test_throws_exception_for_path_too_long(): void
    {
        $page = Page::factory()->make(['slug' => 'test']);

        $longPath = str_repeat('a/', 130); // Creates path > 255 chars

        $this->expectException(WriteException::class);
        $this->expectExceptionMessage('Path too long');

        $this->resolver->resolve($longPath, 'pages', $page);
    }

    public function test_throws_exception_for_empty_path(): void
    {
        $page = Page::factory()->make(['slug' => 'test']);

        $this->expectException(WriteException::class);
        $this->expectExceptionMessage('Path cannot be empty');

        $this->resolver->resolve('', 'pages', $page);
    }

    public function test_gets_directory_from_path(): void
    {
        $directory = $this->resolver->getDirectory('posts/2025/12/hello-world.md');

        $this->assertEquals('posts/2025/12', $directory);
    }

    public function test_gets_directory_for_root_file(): void
    {
        $directory = $this->resolver->getDirectory('test.md');

        $this->assertEquals('.', $directory);
    }

    public function test_checks_if_pattern_has_token(): void
    {
        $this->assertTrue($this->resolver->hasToken('pages/{slug}.md', 'slug'));
        $this->assertTrue($this->resolver->hasToken('{type}/{year}/{month}/{slug}', 'year'));
        $this->assertFalse($this->resolver->hasToken('pages/static.md', 'slug'));
    }

    public function test_gets_all_tokens_from_pattern(): void
    {
        $tokens = $this->resolver->getTokens('{type}/{year}/{month}/{slug}.md');

        $this->assertCount(4, $tokens);
        $this->assertContains('type', $tokens);
        $this->assertContains('year', $tokens);
        $this->assertContains('month', $tokens);
        $this->assertContains('slug', $tokens);
    }

    public function test_gets_empty_array_for_pattern_without_tokens(): void
    {
        $tokens = $this->resolver->getTokens('pages/static.md');

        $this->assertEmpty($tokens);
    }

    public function test_sanitizes_path_component(): void
    {
        $this->assertEquals('hello-world', $this->resolver->sanitizeComponent('hello world'));
        $this->assertEquals('hello-world', $this->resolver->sanitizeComponent('hello--world'));
        $this->assertEquals('hello-world', $this->resolver->sanitizeComponent('  hello-world  '));
        $this->assertEquals('hello', $this->resolver->sanitizeComponent('he<>llo'));
        $this->assertEquals('untitled', $this->resolver->sanitizeComponent(''));
        $this->assertEquals('untitled', $this->resolver->sanitizeComponent('---'));
    }

    public function test_returns_default_pattern_for_pages(): void
    {
        $pattern = PathPatternResolver::getDefaultPattern('pages');

        $this->assertEquals('pages/{slug}.md', $pattern);
    }

    public function test_returns_default_pattern_for_posts(): void
    {
        $pattern = PathPatternResolver::getDefaultPattern('posts');

        $this->assertEquals('posts/{year}/{month}/{slug}.md', $pattern);
    }

    public function test_returns_default_pattern_for_unknown_type(): void
    {
        $pattern = PathPatternResolver::getDefaultPattern('custom');

        $this->assertEquals('{type}/{slug}.md', $pattern);
    }

    public function test_gets_available_tokens(): void
    {
        $tokens = PathPatternResolver::getAvailableTokens();

        $this->assertIsArray($tokens);
        $this->assertArrayHasKey('type', $tokens);
        $this->assertArrayHasKey('slug', $tokens);
        $this->assertArrayHasKey('year', $tokens);
        $this->assertArrayHasKey('month', $tokens);
        $this->assertArrayHasKey('day', $tokens);
        $this->assertArrayHasKey('author_id', $tokens);
        $this->assertArrayHasKey('status', $tokens);
    }

    public function test_previews_pattern_with_sample_data(): void
    {
        $preview = PathPatternResolver::preview('posts/{year}/{month}/{slug}', 'posts');

        $this->assertStringContainsString('posts/', $preview);
        $this->assertStringContainsString('2025', $preview);
        $this->assertStringContainsString('example-post', $preview);
        $this->assertStringEndsWith('.md', $preview);
    }

    public function test_handles_missing_slug_gracefully(): void
    {
        $page = Page::factory()->make(['slug' => null, 'id' => 1]);

        $path = $this->resolver->resolve('pages/{slug}', 'pages', $page);

        $this->assertEquals('pages/untitled.md', $path);
    }

    public function test_resolves_pattern_with_multiple_same_tokens(): void
    {
        $page = Page::factory()->make(['slug' => 'test']);

        $path = $this->resolver->resolve('{slug}/{slug}', 'pages', $page);

        $this->assertEquals('test/test.md', $path);
    }

    public function test_handles_pattern_with_unreplaced_tokens(): void
    {
        // This should log a warning but not fail
        $page = Page::factory()->make(['slug' => 'test']);

        $path = $this->resolver->resolve('pages/{unknown_token}/{slug}', 'pages', $page);

        $this->assertStringContainsString('test', $path);
        $this->assertStringContainsString('{unknown_token}', $path);
    }

    public function test_uses_created_at_for_date_tokens_when_published_at_missing(): void
    {
        $page = Page::factory()->make([
            'slug' => 'test',
            'created_at' => '2025-11-15 10:00:00',
        ]);

        $path = $this->resolver->resolve('pages/{year}/{month}/{slug}', 'pages', $page);

        $this->assertStringContainsString('2025/11', $path);
    }
}
