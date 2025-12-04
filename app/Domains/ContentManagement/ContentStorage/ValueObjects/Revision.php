<?php

declare(strict_types=1);

namespace App\Domains\ContentManagement\ContentStorage\ValueObjects;

use Carbon\Carbon;

/**
 * Represents a single revision of content
 */
readonly class Revision
{
    public function __construct(
        public string $id,              // Version ID, commit hash, or DB ID
        public string $content,         // The actual content at this revision
        public ?string $message,        // Commit message or change description
        public ?string $author,         // Author name
        public ?string $authorEmail,    // Author email
        public Carbon $timestamp,       // When this revision was created
        public ?string $metadata,       // JSON metadata (size, hash, etc)
        public bool $isCurrent,         // Is this the current version?
    ) {}

    /**
     * Convert revision to array format
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'message' => $this->message,
            'author' => $this->author,
            'author_email' => $this->authorEmail,
            'timestamp' => $this->timestamp->toIso8601String(),
            'timestamp_human' => $this->timestamp->diffForHumans(),
            'metadata' => $this->metadata ? json_decode($this->metadata, true) : null,
            'is_current' => $this->isCurrent,
        ];
    }

    /**
     * Get short ID (first 8 characters)
     */
    public function getShortId(): string
    {
        return substr($this->id, 0, 8);
    }

    /**
     * Get content preview (first 200 characters)
     */
    public function getContentPreview(int $length = 200): string
    {
        return strlen($this->content) > $length
            ? substr($this->content, 0, $length).'...'
            : $this->content;
    }
}
