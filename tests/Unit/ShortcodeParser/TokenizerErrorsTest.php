<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions\TokenizerException;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;

test('throws exception for unclosed shortcode tag', function () {
    $input = '[#test';
    $tokenizer = new Tokenizer($input);

    expect(fn () => $tokenizer->tokenize())
        ->toThrow(TokenizerException::class);
});

test('throws exception for unclosed attribute string', function () {
    $input = '[#test title="unclosed][/#test]';
    $tokenizer = new Tokenizer($input);

    expect(fn () => $tokenizer->tokenize())
        ->toThrow(TokenizerException::class);
});

test('throws exception for invalid closing tag syntax', function () {
    $input = '[#test][/test]';
    $tokenizer = new Tokenizer($input);

    expect(fn () => $tokenizer->tokenize())
        ->toThrow(TokenizerException::class);
});
