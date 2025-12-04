<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\TokenType;

test('tokenizes shortcode with attributes', function () {
    $input = '[#hero title="Welcome" size="large"][/#hero]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    expect($tokens[0]->type)->toBe(TokenType::SHORTCODE_OPEN)
        ->and($tokens[0]->value)->toBe('hero')
        ->and($tokens[1]->type)->toBe(TokenType::ATTRIBUTE_NAME)
        ->and($tokens[1]->value)->toBe('title')
        ->and($tokens[3]->type)->toBe(TokenType::ATTRIBUTE_VALUE)
        ->and($tokens[3]->value)->toBe('Welcome');
});

test('handles escaped quotes in attribute values', function () {
    $input = '[#test title="He said \"Hello\""][/#test]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    $valueTokens = array_filter($tokens, fn ($t) => $t->type === TokenType::ATTRIBUTE_VALUE);
    $firstValue = reset($valueTokens);

    expect($firstValue->value)->toBe('He said "Hello"');
});

test('handles multiple quote styles', function () {
    $input = "[#test single='value' double=\"value\"][/#test]";
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    $valueTokens = array_filter($tokens, fn ($t) => $t->type === TokenType::ATTRIBUTE_VALUE);

    expect(count($valueTokens))->toBe(2);
});

test('handles attributes with special characters', function () {
    $input = '[#test data-value="test" my_attribute="value"][/#test]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    $attrNames = array_filter($tokens, fn ($t) => $t->type === TokenType::ATTRIBUTE_NAME);
    $names = array_map(fn ($t) => $t->value, $attrNames);

    expect($names)->toContain('data-value')
        ->and($names)->toContain('my_attribute');
});
