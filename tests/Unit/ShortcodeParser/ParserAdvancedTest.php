<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\Parser\Parser;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\TextNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\MarkdownNode;

test('parses shortcode with text content', function () {
    $input = '[#text]{Hello World}[/#text]';
    $tokens = (new Tokenizer($input))->tokenize();
    $parser = new Parser($tokens);
    $document = $parser->parse();

    $textNode = $document->children[0];

    expect($textNode->children)->toHaveCount(1)
        ->and($textNode->children[0])->toBeInstanceOf(TextNode::class)
        ->and($textNode->children[0]->content)->toBe('Hello World');
});

test('parses shortcode with markdown content', function () {
    $input = '[#text]{# Heading\n\nThis is **bold**.}[/#text]';
    $tokens = (new Tokenizer($input))->tokenize();
    $parser = new Parser($tokens);
    $document = $parser->parse();

    $textNode = $document->children[0];

    expect($textNode->children)->toHaveCount(1)
        ->and($textNode->children[0])->toBeInstanceOf(MarkdownNode::class)
        ->and($textNode->children[0]->content)->toContain('# Heading')
        ->and($textNode->children[0]->content)->toContain('**bold**');
});

test('parses nested shortcodes', function () {
    $input = '[#outer]{[#inner][/#inner]}[/#outer]';
    $tokens = (new Tokenizer($input))->tokenize();
    $parser = new Parser($tokens);
    $document = $parser->parse();

    $outer = $document->children[0];

    expect($outer->tag)->toBe('outer')
        ->and($outer->children)->toHaveCount(1)
        ->and($outer->children[0])->toBeInstanceOf(ShortcodeNode::class)
        ->and($outer->children[0]->tag)->toBe('inner');
});

test('validates mismatched tags', function () {
    $input = '[#test][/#wrong]';
    $tokens = (new Tokenizer($input))->tokenize();
    $parser = new Parser($tokens);

    expect(fn() => $parser->parse())
        ->toThrow(Exception::class);
});
