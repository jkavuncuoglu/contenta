<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\Parser\Parser;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\DocumentNode;
use App\Domains\ContentManagement\Services\ShortcodeParser\AST\ShortcodeNode;

test('parses simple shortcode', function () {
    $input = '[#test][/#test]';
    $tokens = (new Tokenizer($input))->tokenize();
    $parser = new Parser($tokens);
    $document = $parser->parse();

    expect($document)->toBeInstanceOf(DocumentNode::class)
        ->and($document->children)->toHaveCount(1)
        ->and($document->children[0])->toBeInstanceOf(ShortcodeNode::class)
        ->and($document->children[0]->tag)->toBe('test');
});

test('parses shortcode with attributes', function () {
    $input = '[#hero title="Welcome" size="large"][/#hero]';
    $tokens = (new Tokenizer($input))->tokenize();
    $parser = new Parser($tokens);
    $document = $parser->parse();

    $hero = $document->children[0];

    expect($hero->attributes)->toHaveKey('title')
        ->and($hero->attributes['title'])->toBe('Welcome')
        ->and($hero->attributes)->toHaveKey('size')
        ->and($hero->attributes['size'])->toBe('large');
});

test('parses self-closing shortcode', function () {
    $input = '[#image src="photo.jpg" /]';
    $tokens = (new Tokenizer($input))->tokenize();
    $parser = new Parser($tokens);
    $document = $parser->parse();

    $image = $document->children[0];

    expect($image->selfClosing)->toBeTrue()
        ->and($image->attributes['src'])->toBe('photo.jpg')
        ->and($image->children)->toBeEmpty();
});

test('parses multiple top-level shortcodes', function () {
    $input = '[#first][/#first][#second][/#second][#third][/#third]';
    $tokens = (new Tokenizer($input))->tokenize();
    $parser = new Parser($tokens);
    $document = $parser->parse();

    expect($document->children)->toHaveCount(3)
        ->and($document->children[0]->tag)->toBe('first')
        ->and($document->children[1]->tag)->toBe('second')
        ->and($document->children[2]->tag)->toBe('third');
});
