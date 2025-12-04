<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\ShortcodeParserService;

beforeEach(function () {
    $this->service = new ShortcodeParserService;
});

test('parses and renders simple shortcode', function () {
    $markdown = '[#hero title="Welcome"][/#hero]';
    $html = $this->service->parseAndRender($markdown);

    expect($html)->toContain('Welcome')
        ->and($html)->toContain('<section');
});

test('validates correct syntax', function () {
    $validMarkdown = '[#test][/#test]';

    expect($this->service->validate($validMarkdown))->toBeTrue();
});

test('invalidates incorrect syntax', function () {
    $invalidMarkdown = '[#test][/#wrong]';

    expect($this->service->validate($invalidMarkdown))->toBeFalse();
});

test('extracts front matter', function () {
    $content = <<<'MD'
---
title: My Page
author: John Doe
---

[#hero title="Welcome"][/#hero]
MD;

    $result = $this->service->extractFrontMatter($content);

    expect($result['metadata'])->toHaveKey('title')
        ->and($result['metadata']['title'])->toBe('My Page')
        ->and($result['metadata']['author'])->toBe('John Doe')
        ->and($result['content'])->toContain('[#hero');
});

test('handles content without front matter', function () {
    $content = '[#hero title="Test"][/#hero]';

    $result = $this->service->extractFrontMatter($content);

    expect($result['metadata'])->toBeEmpty()
        ->and($result['content'])->toBe($content);
});
