<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\Parser\Parser;
use App\Domains\ContentManagement\Services\ShortcodeParser\Renderer\HtmlRenderer;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;

test('renders features block with nested items', function () {
    $input = '[#features title="Features"]{[#feature-item title="Fast"]{Speed}[/#feature-item]}[/#features]';
    $tokens = (new Tokenizer($input))->tokenize();
    $document = (new Parser($tokens))->parse();
    $renderer = new HtmlRenderer;
    $html = $renderer->render($document);

    expect($html)->toContain('Features')
        ->and($html)->toContain('Fast')
        ->and($html)->toContain('Speed');
});

test('renders CTA block', function () {
    $input = '[#cta title="Ready?" button-text="Start" button-url="/signup"]{Join us}[/#cta]';
    $tokens = (new Tokenizer($input))->tokenize();
    $document = (new Parser($tokens))->parse();
    $renderer = new HtmlRenderer;
    $html = $renderer->render($document);

    expect($html)->toContain('Ready?')
        ->and($html)->toContain('Start')
        ->and($html)->toContain('/signup')
        ->and($html)->toContain('Join us');
});

test('renders stats block', function () {
    $input = '[#stats]{[#stat value="10M+" label="Users"][/#stat]}[/#stats]';
    $tokens = (new Tokenizer($input))->tokenize();
    $document = (new Parser($tokens))->parse();
    $renderer = new HtmlRenderer;
    $html = $renderer->render($document);

    expect($html)->toContain('10M+')
        ->and($html)->toContain('Users');
});
