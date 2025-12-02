<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\HtmlRenderer;
use App\Domains\ContentManagement\Services\ShortcodeParser\Parser\Parser;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;

test('renders hero block', function () {
    $input = '[#hero title="Welcome" subtitle="Get Started" description="Learn more"][/#hero]';
    $tokens = (new Tokenizer($input))->tokenize();
    $document = (new Parser($tokens))->parse();
    $renderer = new HtmlRenderer();
    $html = $renderer->render($document);

    expect($html)->toContain('Welcome')
        ->and($html)->toContain('Get Started')
        ->and($html)->toContain('Learn more')
        ->and($html)->toContain('<section');
});

test('renders text block with markdown', function () {
    $input = '[#text]{# Heading\n\nThis is **bold**.}[/#text]';
    $tokens = (new Tokenizer($input))->tokenize();
    $document = (new Parser($tokens))->parse();
    $renderer = new HtmlRenderer();
    $html = $renderer->render($document);

    expect($html)->toContain('<h1')
        ->and($html)->toContain('Heading')
        ->and($html)->toContain('<strong>')
        ->and($html)->toContain('bold');
});

test('renders button block', function () {
    $input = '[#button url="/action" variant="primary"]{Click Me}[/#button]';
    $tokens = (new Tokenizer($input))->tokenize();
    $document = (new Parser($tokens))->parse();
    $renderer = new HtmlRenderer();
    $html = $renderer->render($document);

    expect($html)->toContain('<a')
        ->and($html)->toContain('href="/action"')
        ->and($html)->toContain('Click Me');
});

test('escapes HTML in attributes', function () {
    $input = '[#text className="<script>alert(\'xss\')</script>"][/#text]';
    $tokens = (new Tokenizer($input))->tokenize();
    $document = (new Parser($tokens))->parse();
    $renderer = new HtmlRenderer();
    $html = $renderer->render($document);

    expect($html)->not->toContain('<script>')
        ->and($html)->toContain('&lt;script&gt;');
});
