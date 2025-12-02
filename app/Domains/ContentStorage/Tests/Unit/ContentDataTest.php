<?php

declare(strict_types=1);

namespace App\Domains\ContentStorage\Tests\Unit;

use App\Domains\ContentStorage\Exceptions\ReadException;
use App\Domains\ContentStorage\Models\ContentData;
use PHPUnit\Framework\TestCase;

/**
 * ContentData Unit Tests
 *
 * Tests the ContentData value object functionality including:
 * - YAML frontmatter parsing
 * - Markdown reconstruction
 * - Content hashing
 * - Immutability
 */
class ContentDataTest extends TestCase
{
    public function test_creates_content_data_from_markdown_with_frontmatter(): void
    {
        $markdown = <<<'MD'
---
title: "Test Page"
slug: "test-page"
status: "published"
author_id: 1
---

# Hello World

This is test content.
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals("# Hello World\n\nThis is test content.", $contentData->content);
        $this->assertEquals('Test Page', $contentData->frontmatter['title']);
        $this->assertEquals('test-page', $contentData->frontmatter['slug']);
        $this->assertEquals('published', $contentData->frontmatter['status']);
        $this->assertEquals(1, $contentData->frontmatter['author_id']);
        $this->assertNotNull($contentData->hash);
        $this->assertGreaterThan(0, $contentData->size);
    }

    public function test_creates_content_data_from_markdown_without_frontmatter(): void
    {
        $markdown = "# Hello World\n\nThis is test content.";

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals($markdown, $contentData->content);
        $this->assertEmpty($contentData->frontmatter);
        $this->assertNotNull($contentData->hash);
    }

    public function test_converts_content_data_back_to_markdown(): void
    {
        $markdown = <<<'MD'
---
title: "Test Page"
slug: "test-page"
---

# Content here
MD;

        $contentData = ContentData::fromMarkdown($markdown);
        $reconstructed = $contentData->toMarkdown();

        $this->assertStringContainsString('title: "Test Page"', $reconstructed);
        $this->assertStringContainsString('slug:', $reconstructed); // YAML doesn't require quotes for simple strings
        $this->assertStringContainsString('test-page', $reconstructed);
        $this->assertStringContainsString('# Content here', $reconstructed);
    }

    public function test_parses_various_yaml_value_types(): void
    {
        $markdown = <<<'MD'
---
title: "String Value"
count: 42
price: 19.99
is_active: true
is_deleted: false
nullable: null
tags: [tag1, tag2, tag3]
---

Content
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertIsString($contentData->frontmatter['title']);
        $this->assertIsInt($contentData->frontmatter['count']);
        $this->assertEquals(42, $contentData->frontmatter['count']);
        $this->assertIsFloat($contentData->frontmatter['price']);
        $this->assertEquals(19.99, $contentData->frontmatter['price']);
        $this->assertIsBool($contentData->frontmatter['is_active']);
        $this->assertTrue($contentData->frontmatter['is_active']);
        $this->assertIsBool($contentData->frontmatter['is_deleted']);
        $this->assertFalse($contentData->frontmatter['is_deleted']);
        $this->assertNull($contentData->frontmatter['nullable']);
        $this->assertIsArray($contentData->frontmatter['tags']);
        $this->assertCount(3, $contentData->frontmatter['tags']);
    }

    public function test_handles_quoted_strings_in_frontmatter(): void
    {
        $markdown = <<<'MD'
---
single: 'single quotes'
double: "double quotes"
unquoted: no quotes
---

Content
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals('single quotes', $contentData->frontmatter['single']);
        $this->assertEquals('double quotes', $contentData->frontmatter['double']);
        $this->assertEquals('no quotes', $contentData->frontmatter['unquoted']);
    }

    public function test_handles_special_characters_in_yaml(): void
    {
        $markdown = <<<'MD'
---
title: "Title with: colon"
description: "Description with #hashtag"
url: "https://example.com?param=value&other=true"
---

Content
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals('Title with: colon', $contentData->frontmatter['title']);
        $this->assertStringContainsString('#hashtag', $contentData->frontmatter['description']);
        $this->assertStringContainsString('param=value&other=true', $contentData->frontmatter['url']);
    }

    public function test_calculates_content_hash_correctly(): void
    {
        $content = '# Test Content';
        $expectedHash = hash('sha256', $content);

        $markdown = "---\ntitle: Test\n---\n\n{$content}";
        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals($expectedHash, $contentData->hash);
        $this->assertEquals($expectedHash, $contentData->getHash());
    }

    public function test_calculates_content_size_correctly(): void
    {
        $content = '# Test Content';
        $expectedSize = strlen($content);

        $markdown = "---\ntitle: Test\n---\n\n{$content}";
        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals($expectedSize, $contentData->size);
        $this->assertEquals($expectedSize, $contentData->getSize());
    }

    public function test_get_method_returns_frontmatter_value(): void
    {
        $markdown = <<<'MD'
---
title: "Test"
author: "John"
---

Content
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals('Test', $contentData->get('title'));
        $this->assertEquals('John', $contentData->get('author'));
        $this->assertNull($contentData->get('missing'));
        $this->assertEquals('default', $contentData->get('missing', 'default'));
    }

    public function test_has_method_checks_frontmatter_key(): void
    {
        $markdown = <<<'MD'
---
title: "Test"
author: "John"
---

Content
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertTrue($contentData->has('title'));
        $this->assertTrue($contentData->has('author'));
        $this->assertFalse($contentData->has('missing'));
    }

    public function test_with_content_creates_new_instance(): void
    {
        $original = ContentData::fromMarkdown("---\ntitle: Test\n---\n\nOriginal");
        $updated = $original->withContent('Updated');

        $this->assertEquals('Original', $original->content);
        $this->assertEquals('Updated', $updated->content);
        $this->assertEquals($original->frontmatter, $updated->frontmatter);
        $this->assertNotEquals($original->hash, $updated->hash);
    }

    public function test_with_frontmatter_creates_new_instance(): void
    {
        $original = ContentData::fromMarkdown("---\ntitle: Original\n---\n\nContent");
        $updated = $original->withFrontmatter(['title' => 'Updated', 'new_key' => 'value']);

        $this->assertEquals('Original', $original->frontmatter['title']);
        $this->assertEquals('Updated', $updated->frontmatter['title']);
        $this->assertEquals('value', $updated->frontmatter['new_key']);
        $this->assertEquals($original->content, $updated->content);
    }

    public function test_merge_frontmatter_combines_arrays(): void
    {
        $original = ContentData::fromMarkdown("---\ntitle: Original\nauthor: John\n---\n\nContent");
        $merged = $original->mergeFrontmatter(['title' => 'Updated', 'new_key' => 'value']);

        $this->assertEquals('Updated', $merged->frontmatter['title']);
        $this->assertEquals('John', $merged->frontmatter['author']);
        $this->assertEquals('value', $merged->frontmatter['new_key']);
    }

    public function test_handles_empty_frontmatter(): void
    {
        $markdown = "---\n\n---\n\nContent";  // Note: actual empty content between markers needs newlines
        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals('Content', $contentData->content);
        $this->assertEmpty($contentData->frontmatter);
    }

    public function test_handles_multiline_values(): void
    {
        $markdown = <<<'MD'
---
title: "Test"
description:
  This is a multiline
  description that spans
  multiple lines
author: "John"
---

Content
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertArrayHasKey('description', $contentData->frontmatter);
        $this->assertStringContainsString('multiline', $contentData->frontmatter['description']);
    }

    public function test_skips_yaml_comments(): void
    {
        $markdown = <<<'MD'
---
# This is a comment
title: "Test"
# Another comment
author: "John"
---

Content
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertArrayHasKey('title', $contentData->frontmatter);
        $this->assertArrayHasKey('author', $contentData->frontmatter);
        $this->assertArrayNotHasKey('# This is a comment', $contentData->frontmatter);
    }

    public function test_builds_yaml_with_proper_formatting(): void
    {
        $frontmatter = [
            'title' => 'Test Page',
            'slug' => 'test-page',
            'count' => 42,
            'active' => true,
            'disabled' => false,
            'nullable' => null,
        ];

        $contentData = new ContentData(
            content: 'Test content',
            frontmatter: $frontmatter
        );

        $markdown = $contentData->toMarkdown();

        $this->assertStringContainsString('title: "Test Page"', $markdown);
        $this->assertStringContainsString('slug: test-page', $markdown);
        $this->assertStringContainsString('count: 42', $markdown);
        $this->assertStringContainsString('active: true', $markdown);
        $this->assertStringContainsString('disabled: false', $markdown);
        $this->assertStringContainsString('nullable: null', $markdown);
    }

    public function test_immutability(): void
    {
        $contentData = new ContentData(
            content: 'Original content',
            frontmatter: ['title' => 'Original']
        );

        // Attempt to modify (this should fail at runtime with readonly properties)
        $this->expectException(\Error::class);
        $contentData->content = 'Modified'; // This will throw Error
    }

    public function test_parses_iso8601_dates(): void
    {
        $markdown = <<<'MD'
---
published_at: "2025-12-02T14:30:00Z"
---

Content
MD;

        $contentData = ContentData::fromMarkdown($markdown);

        $this->assertEquals('2025-12-02T14:30:00Z', $contentData->frontmatter['published_at']);
    }
}
