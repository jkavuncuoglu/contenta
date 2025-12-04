<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\Services\ShortcodeParser\Exceptions;

use Exception;

class ParseException extends Exception
{
    public function __construct(
        string $message,
        public readonly ?int $sourceLine = null,
        public readonly ?int $sourceColumn = null,
    ) {
        $formattedMessage = $message;

        if ($sourceLine !== null) {
            $formattedMessage .= sprintf(' at line %d', $sourceLine);
            if ($sourceColumn !== null) {
                $formattedMessage .= sprintf(', column %d', $sourceColumn);
            }
        }

        parent::__construct($formattedMessage);
    }
}
