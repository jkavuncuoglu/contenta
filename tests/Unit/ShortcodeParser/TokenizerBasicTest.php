<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\TokenType;

test('tokenizes simple shortcode', function () {
    $input = '[#test][/#test]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    expect($tokens)->toHaveCount(3)
        ->and($tokens[0]->type)->toBe(TokenType::SHORTCODE_OPEN)
        ->and($tokens[0]->value)->toBe('test')
        ->and($tokens[1]->type)->toBe(TokenType::SHORTCODE_CLOSE)
        ->and($tokens[1]->value)->toBe('test')
        ->and($tokens[2]->type)->toBe(TokenType::EOF);
});

test('tokenizes self-closing shortcode', function () {
    $input = '[#image src="photo.jpg" /]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    expect($tokens[0]->type)->toBe(TokenType::SHORTCODE_OPEN)
        ->and($tokens[0]->value)->toBe('image');
});

test('tokenizes comments', function () {
    $input = '[#!-- This is a comment --]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    expect($tokens[0]->type)->toBe(TokenType::COMMENT)
        ->and($tokens[0]->value)->toContain('This is a comment');
});

test('tracks line and column numbers', function () {
    $input = "[#test]\nSecond line\n[/#test]";
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    expect($tokens[0]->line)->toBe(1)
        ->and($tokens[0]->column)->toBe(1);
});
