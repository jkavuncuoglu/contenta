<?php

use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\Tokenizer;
use App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer\TokenType;

test('tokenizes shortcode with text content', function () {
    $input = '[#text]{Hello World}[/#text]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    $contentIndex = null;
    foreach ($tokens as $i => $token) {
        if ($token->type === TokenType::CONTENT_OPEN) {
            $contentIndex = $i;
            break;
        }
    }

    expect($contentIndex)->not->toBeNull()
        ->and($tokens[$contentIndex + 1]->type)->toBe(TokenType::TEXT)
        ->and($tokens[$contentIndex + 1]->value)->toBe('Hello World');
});

test('tokenizes nested shortcodes', function () {
    $input = '[#outer]{[#inner][/#inner]}[/#outer]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    expect($tokens[0]->type)->toBe(TokenType::SHORTCODE_OPEN)
        ->and($tokens[0]->value)->toBe('outer')
        ->and($tokens[1]->type)->toBe(TokenType::CONTENT_OPEN);
});

test('handles empty content', function () {
    $input = '[#test]{}[/#test]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    $hasContentOpen = false;
    $hasContentClose = false;

    foreach ($tokens as $token) {
        if ($token->type === TokenType::CONTENT_OPEN) {
            $hasContentOpen = true;
        }
        if ($token->type === TokenType::CONTENT_CLOSE) {
            $hasContentClose = true;
        }
    }

    expect($hasContentOpen)->toBeTrue()
        ->and($hasContentClose)->toBeTrue();
});

test('handles unicode characters', function () {
    $input = '[#test title="Hello 世界 🌍"][/#test]';
    $tokenizer = new Tokenizer($input);
    $tokens = $tokenizer->tokenize();

    $valueTokens = array_filter($tokens, fn($t) => $t->type === TokenType::ATTRIBUTE_VALUE);
    $firstValue = reset($valueTokens);

    expect($firstValue->value)->toBe('Hello 世界 🌍');
});
