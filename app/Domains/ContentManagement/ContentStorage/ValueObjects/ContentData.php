<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\ValueObjects;

/**
 * Simple Content Data Value Object
 *
 * Represents content for Posts and Pages with markdown, HTML, and table of contents.
 * This is a simplified version used by controllers, distinct from the full
 * ContentStorage\Models\ContentData used by repositories.
 */
readonly class ContentData
{
    public function __construct(
        public string $markdown,
        public ?string $html = null,
        public ?array $tableOfContents = null,
    ) {}

    /**
     * Convert to array
     *
     * @return array{markdown: string, html: string|null, table_of_contents: array|null}
     */
    public function toArray(): array
    {
        return [
            'markdown' => $this->markdown,
            'html' => $this->html,
            'table_of_contents' => $this->tableOfContents,
        ];
    }

    /**
     * Convert to JSON string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
