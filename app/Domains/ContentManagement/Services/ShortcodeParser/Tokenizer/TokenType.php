<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Tokenizer;

enum TokenType: string
{
    case SHORTCODE_OPEN = 'SHORTCODE_OPEN';           // [#tag
    case SHORTCODE_CLOSE = 'SHORTCODE_CLOSE';         // [/#tag]
    case SHORTCODE_SELF_CLOSE = 'SHORTCODE_SELF_CLOSE'; // /]
    case ATTRIBUTE_NAME = 'ATTRIBUTE_NAME';           // key
    case ATTRIBUTE_VALUE = 'ATTRIBUTE_VALUE';         // "value"
    case EQUALS = 'EQUALS';                           // =
    case CONTENT_OPEN = 'CONTENT_OPEN';               // {
    case CONTENT_CLOSE = 'CONTENT_CLOSE';             // }
    case TEXT = 'TEXT';                               // Plain text
    case MARKDOWN = 'MARKDOWN';                       // Markdown content
    case WHITESPACE = 'WHITESPACE';                   // Spaces, tabs, newlines
    case EOF = 'EOF';                                 // End of file
    case COMMENT = 'COMMENT';                         // [#!-- comment --]
}
